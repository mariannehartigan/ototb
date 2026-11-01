<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use App\Services\ExpensesServices\DistributeExpensesService;
use App\Services\ExpensesServices\InsertExpensesService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MakePlanService
{
    public function __construct(
        private InsertIncomesService $insertIncomes,
        private InsertExpensesService $insertExpenses,
        private DistributeExpensesService $distributeExpenses
    ) {}

    public function makePlan(Carbon $date): void
    {
        DB::transaction(function () use ($date) {
            $month = $date->copy()->startOfMonth();
            $nextMonth = $month->copy()->addMonth();
            $nextMonthEnd = $nextMonth->copy()->endOfMonth();
            $userId = Auth::id();

            // Generate the current month's incomes and expenses.
            $this->insertIncomes->insertIncomes($month);
            $this->insertExpenses->insertExpenses($month);

            $incomes = $this->getMonthlyIncomes($month);
            $expenses = $this->getMonthlyExpenses($month);

            // Consider the entire next month, not just bills before its first income.
            $cutoff = $nextMonthEnd->copy()->addDay();

            $incomeIds = $incomes->pluck('id');

            // Include next-month expenses already assigned to October incomes.
            // Unassigned next-month expenses are also eligible for projection.
            $alreadyFundedNextExpenses = Expense::where(
                    'user_id',
                    $userId
                )
                ->whereIn('income_id', $incomeIds)
                ->whereDate('date', '>=', $nextMonth)
                ->whereDate('date', '<', $cutoff)
                ->where('paid', false)
                ->where('automatically_generated_tithe', false)
                ->get();

            foreach ($alreadyFundedNextExpenses as $expense) {
                $this->prepareExpense($expense);
                $expenses->push($expense);
            }

            // Existing, unassigned November expenses should be considered too.
            // Do not touch expenses already assigned to November's own incomes.
            $unassignedNextExpenses = Expense::where('user_id', $userId)
                ->whereNull('income_id')
                ->whereDate('date', '>=', $nextMonth)
                ->whereDate('date', '<', $cutoff)
                ->where('paid', false)
                ->where('automatically_generated_tithe', false)
                ->get();

            foreach ($unassignedNextExpenses as $expense) {
                $this->prepareExpense($expense);
                $expenses->push($expense);
            }

            // Project all next-month expenses.
            $projectedNextExpenses = $this->insertExpenses
                ->projectExpenses($nextMonth)
                ->filter(fn ($expense) =>
                    Carbon::parse($expense->date)->lt($cutoff)
                );

            // Avoid generating duplicate expense occurrences.
            $existingNextOccurrences = Expense::where(
                    'user_id',
                    $userId
                )
                ->whereDate('date', '>=', $nextMonth)
                ->whereDate('date', '<=', $nextMonthEnd)
                ->whereNotNull('planned_expense_id')
                ->get(['planned_expense_id', 'date'])
                ->map(fn ($expense) =>
                    $this->occurrenceKey(
                        $expense->planned_expense_id,
                        $expense->date
                    )
                )
                ->all();

            $projectedNextExpenses = $projectedNextExpenses
                ->reject(fn ($expense) =>
                    in_array(
                        $this->occurrenceKey(
                            $expense->planned_expense_id,
                            $expense->date
                        ),
                        $existingNextOccurrences,
                        true
                    )
                )
                ->values();

            foreach ($projectedNextExpenses as $index => $expense) {
                $expense->plan_key = 'projected-' . $index;
            }

            // Reconstruct each income's opening balance.
            foreach ($incomes as $income) {
                $reconsidered = $expenses
                    ->where('income_id', $income->id)
                    ->sum('amount');

                $unpaidAutoTithe = Expense::where(
                        'user_id',
                        $userId
                    )
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', false)
                    ->sum('amount');

                $income->opening_balance = round(
                    (float) $income->remaining
                        + (float) $reconsidered
                        + (float) $unpaidAutoTithe,
                    2
                );

                $income->paid_manual_tithe = Expense::where(
                        'user_id',
                        $userId
                    )
                    ->where('income_id', $income->id)
                    ->where('tithe', true)
                    ->where('automatically_generated_tithe', false)
                    ->where('paid', true)
                    ->sum('amount');

                $income->paid_auto_tithe = Expense::where(
                        'user_id',
                        $userId
                    )
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', true)
                    ->sum('amount');
            }

            $allExpenses = $expenses
                ->concat($projectedNextExpenses)
                ->values();

            // Distribute expenses among eligible incomes.
            $result = $this->distributeExpenses
                ->distributeExpenses($incomes, $allExpenses, $month);

            $incomeByKey = $incomes->keyBy('plan_key');

            // Update existing unpaid expenses.
            foreach ($expenses as $expense) {
                $assignedKey = $result['assignments'][
                    $expense->plan_key
                ] ?? null;

                $chosenIncome = $assignedKey !== null
                    ? $incomeByKey->get($assignedKey)
                    : null;

                Expense::where('user_id', $userId)
                    ->whereKey($expense->id)
                    ->where('paid', false)
                    ->update([
                        'income_id' => $chosenIncome?->id,
                    ]);
            }

            // If a previously reserved November bill no longer fits, release
            // its October income assignment; November can fund it instead.
            // The record itself stays in place, preventing duplicate occurrences.

            // Save next month's expenses if they
            // are funded by an income from this month.
            foreach ($projectedNextExpenses as $expense) {
                $assignedKey = $result['assignments'][
                    $expense->plan_key
                ] ?? null;

                if ($assignedKey === null) {
                    continue;
                }

                $chosenIncome = $incomeByKey->get($assignedKey);

                if (!$chosenIncome) {
                    continue;
                }

                if ($expense->planned_expense_id === null) {
                    throw new RuntimeException(
                        'Projected expense has no planned_expense_id: '
                        . $expense->description
                    );
                }

                $existing = Expense::where('user_id', $userId)
                    ->where(
                        'planned_expense_id',
                        $expense->planned_expense_id
                    )
                    ->whereDate(
                        'date',
                        Carbon::parse($expense->date)->toDateString()
                    )
                    ->first();

                if ($existing) {
                    continue;
                }

                Expense::create([
                    'user_id' => $userId,
                    'planned_expense_id' =>
                        $expense->planned_expense_id,
                    'month_year' =>
                        Carbon::parse($expense->date)
                            ->startOfMonth()->toDateString(),
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'date' =>
                        Carbon::parse($expense->date)->toDateString(),
                    'fixed_or_variable' =>
                        $expense->fixed_or_variable,
                    'account_taken_from' =>
                        $expense->account_taken_from,
                    'manual_or_automatic' =>
                        $expense->manual_or_automatic,
                    'tithe' => (bool) $expense->tithe,
                    'automatically_generated_tithe' => false,
                    'paid' => false,
                    'notes' => $expense->notes,
                    'income_id' => $chosenIncome->id,
                ]);
            }

            // Update remaining balances and automatic tithes.
            foreach ($incomes as $income) {
                $balance = $result['income_balances'][
                    $income->plan_key
                ];

                Income::where('user_id', $userId)
                    ->whereKey($income->id)
                    ->update([
                        'remaining' => $balance['remaining'],
                    ]);

                // Never delete paid automatic tithes.
                Expense::where('user_id', $userId)
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', false)
                    ->delete();

                if ($balance['automatic_tithe'] > 0) {
                    Expense::create([
                        'user_id' => $userId,
                        'income_id' => $income->id,
                        'month_year' => $month->toDateString(),
                        'date' => Carbon::parse($income->date)
                            ->toDateString(),
                        'description' => 'Automatic tithe',
                        'amount' => $balance['automatic_tithe'],
                        'tithe' => true,
                        'automatically_generated_tithe' => true,
                        'paid' => false,
                    ]);
                }
            }
        });
    }

    private function getMonthlyIncomes(Carbon $month): Collection
    {
        return Income::where('user_id', Auth::id())
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->with('plannedIncome.plannedIncomeDetails')
            ->orderBy('date')
            ->get()
            ->map(function ($income) {
                $detail = $income->plannedIncome
                    ?->plannedIncomeDetails
                    ?->filter(fn ($detail) =>
                        $detail->effective_date === null
                        || Carbon::parse($detail->effective_date)
                            ->lte(Carbon::parse($income->date))
                    )
                    ->sortBy('effective_date')
                    ->last();

                $income->plan_key = 'income-' . $income->id;

                $income->classification = in_array(
                    $detail?->frequency,
                    ['quarterly', 'yearly', 'onetime'],
                    true
                ) ? 'backup' : 'regular';

                $income->tithe = (bool) ($detail?->tithe ?? false);

                $income->assign_no_expenses_except_tithe =
                    (bool) (
                        $detail?->assign_no_expenses_except_tithe
                        ?? false
                    );

                return $income;
            });
    }

    private function getMonthlyExpenses(Carbon $month): Collection
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->where('paid', false)
            ->where('automatically_generated_tithe', false)
            ->with('income', 'plannedExpense.plannedExpenseDetails')
            ->orderBy('date')
            ->get();

        // Preserve expenses funded by an earlier month.
        return $expenses
            ->reject(function ($expense) use ($month) {
                if (!$expense->income_id || !$expense->income) {
                    return false;
                }

                return Carbon::parse($expense->income->date)
                    ->lt($month);
            })
            ->map(function ($expense) {
                $this->prepareExpense($expense);

                return $expense;
            })
            ->values();
    }

    private function prepareExpense(Expense $expense): void
    {
        $expense->plan_key = 'expense-' . $expense->id;

        $expense->loadMissing(
            'plannedExpense.plannedExpenseDetails'
        );

        $detail = $expense->plannedExpense
            ?->plannedExpenseDetails
            ?->filter(fn ($detail) =>
                $detail->effective_date === null
                || Carbon::parse($detail->effective_date)
                    ->lte(Carbon::parse($expense->date))
            )
            ->sortBy('effective_date')
            ->last();

        $expense->planned_income_id =
            $detail?->planned_income_id;
    }

    private function occurrenceKey(
        mixed $plannedId,
        mixed $date
    ): string {
        return (string) $plannedId
            . '|'
            . Carbon::parse($date)->toDateString();
    }
}
