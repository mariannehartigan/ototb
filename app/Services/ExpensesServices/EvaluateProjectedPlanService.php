<?php

namespace App\Services\ExpensesServices;

use App\Services\IncomeProjectionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvaluateProjectedPlanService
{
    public function __construct(
        private IncomeProjectionService $incomeProjection,
        private ExpenseProjectionService $expenseProjection
    ) {}

    public function evaluate(
        Carbon $start,
        array $fundingChoices = [],
        int $months = 24
    ): array {

        // Include an extra month at the beginning so we
        // can examine expenses funded in advance.
        $projectionStart = $start->copy()
            ->startOfMonth()
            ->subMonthNoOverflow();

        $incomes = $this->incomeProjection->project(
            $projectionStart,
            $months + 2
        );

        $expenses = $this->expenseProjection->project(
            $projectionStart,
            $months + 2,
            $fundingChoices
        );

        return $this->analyze(
            $incomes,
            $expenses,
            $start->copy()->startOfMonth(),
            $months
        );
    }

    private function analyze(
        Collection $incomes,
        Collection $expenses,
        Carbon $start,
        int $months
    ): array {

        $regularShortfall = 0;
        $cashflowShortfall = 0;
        $backupUsed = 0;
        $hardViolations = 0;
        $balancePenalty = 0;

        $end = $start->copy()
            ->addMonthsNoOverflow($months)
            ->subDay();

        /*
         * Classify projected income.
         */

        $regularIncomes = $incomes->filter(
            fn ($income) => $income->classification === 'regular'
        );

        $backupIncomes = $incomes->filter(
            fn ($income) => $income->classification === 'backup'
        );

        $protectedIncomes = $incomes->filter(
            fn ($income) => $income->classification === 'protected'
        );

        /*
         * Examine each funding month independently.
         */

        $month = $start->copy();

        while ($month->lte($end)) {

            $monthKey = $month->format('Y-m');

            $monthRegularIncomes = $regularIncomes->filter(
                fn ($income) =>
                    Carbon::parse($income->date)->format('Y-m')
                    === $monthKey
            );

            $monthBackupIncomes = $backupIncomes->filter(
                fn ($income) =>
                    Carbon::parse($income->date)->format('Y-m')
                    === $monthKey
            );

            $monthExpenses = $expenses->filter(
                fn ($expense) =>
                    $expense->funding_month === $monthKey
            );

            $regularAmount = $monthRegularIncomes->sum('amount');
            $backupAmount = $monthBackupIncomes->sum('amount');
            $expenseAmount = $monthExpenses->sum('amount');

            /*
             * Reserve tithe on eligible incomes.
             *
             * This is a provisional estimate. The final
             * tithe allocator will account for manual
             * tithe expenses and the global tithe setting.
             */

            $estimatedTithe = $incomes
                ->filter(
                    fn ($income) =>
                        Carbon::parse($income->date)->format('Y-m')
                            === $monthKey
                        && $income->tithe
                )
                ->sum(fn ($income) => $income->amount * 0.10);

            $regularTithe = $monthRegularIncomes
                ->filter(fn ($income) => $income->tithe)
                ->sum(fn ($income) => $income->amount * 0.10);

            $regularAvailable = $regularAmount - $regularTithe;

            $monthShortfall = max(
                0,
                $expenseAmount - $regularAvailable
            );

            $regularShortfall += $monthShortfall;

            /*
             * Backup income is available only when
             * regular income cannot cover the month.
             */

            $backupNeeded = min(
                $monthShortfall,
                $backupAmount
            );

            $backupUsed += $backupNeeded;

            /*
             * A funding month with insufficient
             * regular and backup income still has
             * an unresolved shortage.
             */

            $unresolvedShortfall = max(
                0,
                $monthShortfall - $backupAmount
            );

            /*
             * This metric is a provisional monthly
             * shortage indicator, not a count of
             * individual invalid assignments.
             */

            if ($unresolvedShortfall > 0.000001) {
                $hardViolations++;
            }

            /*
             * Estimate the chronological cash flow
             * for expenses funded in this month.
             */

            $events = collect();

            foreach ($monthRegularIncomes as $income) {

                $tithe = $income->tithe
                    ? $income->amount * 0.10
                    : 0;

                $events->push([
                    'date' => $income->date,
                    'type' => 'income',
                    'amount' => $income->amount - $tithe,
                ]);
            }

            foreach ($monthExpenses as $expense) {

                // An advance-funded expense reserves
                // money in its funding month.
                $eventDate = $expense->assign_to_following_month
                    ? $month->copy()->endOfMonth()->toDateString()
                    : $expense->date;

                $events->push([
                    'date' => $eventDate,
                    'type' => 'expense',
                    'amount' => -$expense->amount,
                ]);
            }

            $events = $events->sort(function ($a, $b) {

                $dateComparison = strcmp(
                    $a['date'],
                    $b['date']
                );

                if ($dateComparison !== 0) {
                    return $dateComparison;
                }

                // Income is processed before expenses
                // when both occur on the same date.
                return $a['type'] === 'income' ? -1 : 1;
            });

            $runningBalance = 0;
            $lowestBalance = 0;

            foreach ($events as $event) {

                $runningBalance += $event['amount'];

                $lowestBalance = min(
                    $lowestBalance,
                    $runningBalance
                );
            }

            $cashflowShortfall += abs(
                min(0, $lowestBalance)
            );

            /*
             * This is a preliminary balance penalty.
             * The final allocator will measure the
             * percentage remaining on each income.
             */

            if ($regularAmount > 0) {

                $remainingPercentage =
                    ($regularAvailable - $expenseAmount)
                    / $regularAmount;

                $balancePenalty += abs(
                    min(0, $remainingPercentage)
                );
            }

            $month->addMonthNoOverflow();
        }

        /*
         * Explicit planned-income restrictions.
         *
         * Protected income cannot fund ordinary
         * expenses, even when explicitly requested.
         */

        foreach ($expenses as $expense) {

            if (
                !$expense->planned_income_id
                || $expense->tithe
            ) {
                continue;
            }

            $matchingIncomes = $incomes->filter(
                fn ($income) =>
                    $income->planned_income_id
                        == $expense->planned_income_id
                    && Carbon::parse($income->date)->format('Y-m')
                        === $expense->funding_month
            );

            if ($matchingIncomes->isEmpty()) {
                $hardViolations++;
                continue;
            }

            if (
                $matchingIncomes->every(
                    fn ($income) =>
                        $income->classification === 'protected'
                )
            ) {
                $hardViolations++;
            }
        }

        return [
            'hard_violations' => $hardViolations,
            'regular_shortfall' => round($regularShortfall, 2),
            'cashflow_shortfall' => round($cashflowShortfall, 2),
            'backup_used' => round($backupUsed, 2),
            'balance_penalty' => round($balancePenalty, 6),
        ];
    }
}
