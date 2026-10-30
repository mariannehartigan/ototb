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

class MakePlanService
{
    public function __construct(
        private InsertIncomesService $insertIncomes,
        private InsertExpensesService $insertExpenses,
        private DistributeExpensesService $distributeExpenses,
    ) {}

    public function makePlan(Carbon $date): void
    {
        DB::transaction(function () use ($date) {
            $month = $date->copy()->startOfMonth();
            $nextMonth = $month->copy()->addMonth();
            $end = $nextMonth->copy()->endOfMonth();
            $userId = Auth::id();

            // Generate occurrences for both calendar months. The insertion
            // services MUST be idempotent (see integration notes).
            foreach ([$month, $nextMonth] as $calendarMonth) {
                $this->insertIncomes->insertIncomes($calendarMonth);
                $this->insertExpenses->insertExpenses($calendarMonth);
            }

            $expenses = $this->getExpenses($month, $end);
            $incomes = $this->getIncomes($end);

            // Restore only amounts from UNPAID expenses that are about to
            // be redistributed. Existing paid expenses and assignments
            // outside this window stay deducted from remaining.
            foreach ($incomes as $income) {
                $reconsidered = $expenses
                    ->where('income_id', $income->id)
                    ->sum('amount');

                $isReceiptMonth = Carbon::parse($income->date)->greaterThanOrEqualTo($month);
                $existingAutoTithe = $isReceiptMonth
                    ? Expense::where('user_id', $userId)
                        ->where('income_id', $income->id)
                        ->where('automatically_generated_tithe', true)
                        ->where('paid', false)
                        ->sum('amount')
                    : 0;

                $income->opening_balance = round(
                    (float) $income->remaining
                    + (float) $reconsidered
                    + (float) $existingAutoTithe,
                    2
                );

                $hasPaidAutoTithe = Expense::where('user_id', $userId)
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', true)
                    ->exists();

                $income->tithe_already_accounted = !$isReceiptMonth || $hasPaidAutoTithe;
            }

            $result = $this->distributeExpenses->distributeExpenses($incomes, $expenses);

            // Clear only the unpaid expenses in this recalculation window.
            // Reapply the new assignments below, leaving paid rows alone.
            $expenseIds = $expenses->pluck('id')->all();

            if ($expenseIds) {
                Expense::where('user_id', $userId)
                    ->whereIn('id', $expenseIds)
                    ->where('paid', false)
                    ->update(['income_id' => null]);
            }

            foreach ($result['assignments'] as $expenseId => $incomeId) {
                Expense::where('user_id', $userId)
                    ->whereKey($expenseId)
                    ->where('paid', false)
                    ->update(['income_id' => $incomeId]);
            }

            foreach ($incomes as $income) {
                $balance = $result['income_balances'][$income->id];

                Income::where('user_id', $userId)
                    ->whereKey($income->id)
                    ->update(['remaining' => $balance['remaining']]);

                // Earlier incomes have already had their tithes accounted for.
                if (Carbon::parse($income->date)->lt($month)) {
                    continue;
                }

                Expense::where('user_id', $userId)
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', false)
                    ->delete();

                if ($balance['automatic_tithe'] <= 0) {
                    continue;
                }

                // A paid automatic tithe is already a fixed commitment.
                // Never create another automatic tithe for that income.
                $paidAutoExists = Expense::where('user_id', $userId)
                    ->where('income_id', $income->id)
                    ->where('automatically_generated_tithe', true)
                    ->where('paid', true)
                    ->exists();

                if ($paidAutoExists) {
                    continue;
                }

                Expense::create([
                    'planned_expense_id' => null,
                    'date' => Carbon::parse($income->date)->toDateString(),
                    'month_year' => Carbon::parse($income->date)->startOfMonth()->toDateString(),
                    'description' => 'Automatic tithe',
                    'amount' => $balance['automatic_tithe'],
                    'tithe' => true,
                    'automatically_generated_tithe' => true,
                    'paid' => false,
                    'income_id' => $income->id,
                    'user_id' => $userId,
                ]);
            }
        });
    }

    private function getIncomes(Carbon $end): Collection
    {
        return Income::where('user_id', Auth::id())
            ->whereDate('date', '<=', $end)
            ->with('plannedIncome.plannedIncomeDetails')
            ->orderBy('date')
            ->get()
            ->filter(function ($income) {
                $detail = $income->plannedIncome?->plannedIncomeDetails
                    ?->filter(fn ($d) => $d->effective_date === null
                        || Carbon::parse($d->effective_date)->lte($income->date))
                    ->sortBy([['effective_date', 'asc'], ['id', 'asc']])
                    ->last();

                if (!$detail) {
                    return false;
                }

                $income->classification = in_array(
                    $detail->frequency,
                    ['quarterly', 'yearly', 'onetime'],
                    true
                ) ? 'backup' : 'regular';

                $income->tithe = (bool) $detail->tithe;

                $income->assign_no_expenses_except_tithe =
                    (bool) $detail->assign_no_expenses_except_tithe;

                return true;
            })
            ->values();
    }

    private function getExpenses(Carbon $start, Carbon $end): Collection
    {
        return Expense::where('user_id', Auth::id())
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->where('automatically_generated_tithe', false)
            ->where('paid', false)
            ->with('plannedExpense.plannedExpenseDetails')
            ->orderBy('date')
            ->get()
            ->map(function ($expense) {
                $detail = $expense->plannedExpense?->plannedExpenseDetails
                    ?->filter(fn ($d) => $d->effective_date === null
                        || Carbon::parse($d->effective_date)->lte($expense->date))
                    ->sortBy([['effective_date', 'asc'], ['id', 'asc']])
                    ->last();

                $expense->planned_income_id = $detail?->planned_income_id;

                return $expense;
            });
    }
}
