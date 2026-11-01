<?php

namespace App\Services\ExpensesServices;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DistributeExpensesService
{
    public function distributeExpenses(
        Collection $incomes,
        Collection $expenses,
        Carbon $month
    ): array {
        $balances = [];
        $assignments = [];
        $unassigned = [];

        foreach ($incomes as $income) {
            $key = (string) $income->plan_key;

            $balances[$key] = [
                'income' => $income,
                'opening' => (float) $income->opening_balance,
                'remaining' => (float) $income->opening_balance,
                'automatic_tithe' => 0,
                'expenses' => [],
            ];

            $this->recalculate($balances[$key]);
        }

        $monthEnd = $month->copy()->endOfMonth();

        $currentExpenses = $expenses
            ->filter(fn ($expense) =>
                Carbon::parse($expense->date)->lte($monthEnd)
            )
            ->sortBy([
                ['date', 'asc'],
                ['amount', 'desc'],
            ])
            ->values();

        $futureExpenses = $expenses
            ->filter(fn ($expense) =>
                Carbon::parse($expense->date)->gt($monthEnd)
            )
            ->sortBy([
                ['amount', 'desc'],
                ['date', 'asc'],
            ])
            ->values();

        // 1. Assign all current-month expenses.
        foreach ($currentExpenses as $expense) {
            $eligible = $this->eligibleIncomes(
                $expense,
                $balances
            );

            if (empty($eligible)) {
                $unassigned[] = [
                    'expense' => $expense,
                    'reason' => 'No eligible income by due date',
                ];

                continue;
            }

            $chosenIncome = $this->chooseCurrentIncome(
                $expense,
                $eligible,
                $balances
            );

            $this->assignExpense(
                $expense,
                $chosenIncome,
                $balances,
                $assignments
            );
        }

        // 2. Improve the current-month distribution.
        $this->improveCurrentAssignments(
            $currentExpenses,
            $balances,
            $assignments
        );

        // 3. Optimize future expenses as a group.
        $futureAssignments = $this->optimizeFutureExpenses(
            $futureExpenses,
            $balances
        );

        foreach ($futureExpenses as $expense) {
            $expenseKey = (string) $expense->plan_key;

            if (!isset($futureAssignments[$expenseKey])) {
                $unassigned[] = [
                    'expense' => $expense,
                    'reason' => 'Left for next month',
                ];

                continue;
            }

            $this->assignExpense(
                $expense,
                $futureAssignments[$expenseKey],
                $balances,
                $assignments
            );
        }

        return [
            'assignments' => $assignments,
            'unassigned' => $unassigned,
            'income_balances' => $balances,
        ];
    }

    private function eligibleIncomes(
        object $expense,
        array $balances
    ): array {
        $eligible = [];

        foreach ($balances as $key => $balance) {
            $income = $balance['income'];

            if (
                Carbon::parse($income->date)->startOfDay()
                    ->gt(Carbon::parse($expense->date)->startOfDay())
            ) {
                continue;
            }

            if (
                $income->assign_no_expenses_except_tithe
                && !$expense->tithe
            ) {
                continue;
            }

            if (
                $expense->planned_income_id !== null
                && $expense->planned_income_id
                    != $income->planned_income_id
            ) {
                continue;
            }

            $eligible[] = (string) $key;
        }

        return $eligible;
    }

    private function chooseCurrentIncome(
        object $expense,
        array $eligible,
        array $balances
    ): string {
        $candidates = [];

        foreach ($eligible as $key) {
            $trial = $balances[$key];

            $trial['expenses'][
                (string) $expense->plan_key
            ] = $expense;

            $this->recalculate($trial);

            $income = $balances[$key]['income'];

            $candidates[] = [
                'key' => $key,
                'remaining' => $trial['remaining'],
                'negative' => max(0, -$trial['remaining']),
                'backup' =>
                    $income->classification === 'backup',
                'percentage' =>
                    $trial['remaining']
                    / max(0.01, (float) $income->amount),
            ];
        }

        // Use regular incomes first, unless a backup income
        // is needed to avoid a negative balance.
        $regularCanCover = array_filter(
            $candidates,
            fn ($candidate) =>
                !$candidate['backup']
                && $candidate['remaining'] >= 0
        );

        $anyCanCover = array_filter(
            $candidates,
            fn ($candidate) =>
                $candidate['remaining'] >= 0
        );

        if (!empty($regularCanCover)) {
            $candidates = array_values($regularCanCover);
        } elseif (!empty($anyCanCover)) {
            $candidates = array_values($anyCanCover);
        } else {
            $regular = array_filter(
                $candidates,
                fn ($candidate) => !$candidate['backup']
            );

            if (!empty($regular)) {
                $candidates = array_values($regular);
            }
        }

        usort($candidates, function ($a, $b) {
            if ($a['negative'] != $b['negative']) {
                return $a['negative'] <=> $b['negative'];
            }

            return $b['percentage'] <=> $a['percentage'];
        });

        return $candidates[0]['key'];
    }

    private function improveCurrentAssignments(
        Collection $expenses,
        array &$balances,
        array &$assignments
    ): void {
        for ($iteration = 0; $iteration < 300; $iteration++) {
            $bestScore = $this->currentScore($balances);
            $bestMove = null;

            foreach ($expenses as $expense) {
                $expenseKey = (string) $expense->plan_key;

                if (!isset($assignments[$expenseKey])) {
                    continue;
                }

                $from = $assignments[$expenseKey];

                foreach (
                    $this->eligibleIncomes($expense, $balances)
                    as $to
                ) {
                    if ($from === $to) {
                        continue;
                    }

                    $trial = $balances;

                    $this->removeExpense(
                        $expense,
                        $from,
                        $trial
                    );

                    $this->putExpense(
                        $expense,
                        $to,
                        $trial
                    );

                    $score = $this->currentScore($trial);

                    if ($score < $bestScore - 0.000001) {
                        $bestScore = $score;
                        $bestMove = [$expense, $from, $to];
                    }
                }
            }

            if ($bestMove === null) {
                break;
            }

            [$expense, $from, $to] = $bestMove;

            $this->removeExpense(
                $expense,
                $from,
                $balances
            );

            $this->assignExpense(
                $expense,
                $to,
                $balances,
                $assignments
            );
        }
    }

    private function currentScore(array $balances): float
    {
        $negative = 0;
        $backupUsed = 0;
        $regular = [];

        foreach ($balances as $balance) {
            $income = $balance['income'];

            $negative += max(
                0,
                -$balance['remaining']
            );

            if ($income->classification === 'backup') {
                foreach ($balance['expenses'] as $expense) {
                    if ($expense->planned_income_id === null) {
                        $backupUsed += (float) $expense->amount;
                    }
                }
            } elseif (
                !$income->assign_no_expenses_except_tithe
                && (float) $income->amount > 0
            ) {
                $regular[] = $balance;
            }
        }

        $imbalance = 0;

        if (count($regular) > 1) {
            $totalIncome = array_sum(array_map(
                fn ($balance) =>
                    (float) $balance['income']->amount,
                $regular
            ));

            $totalRemaining = array_sum(array_map(
                fn ($balance) => $balance['remaining'],
                $regular
            ));

            if ($totalIncome > 0) {
                $target = $totalRemaining / $totalIncome;

                foreach ($regular as $balance) {
                    $amount = (float) $balance['income']->amount;

                    $percentage =
                        $balance['remaining'] / $amount;

                    $imbalance += $amount
                        * pow($percentage - $target, 2);
                }
            }
        }

        return $negative * 1000000
            + $backupUsed * 100
            + $imbalance;
    }

    private function optimizeFutureExpenses(
        Collection $expenses,
        array $balances
    ): array {
        $items = $expenses->values()->all();

        // Use cents internally to avoid floating-point
        // comparisons around zero.
        $bestAssignments = [];
        $bestFunded = -1;
        $bestScore = INF;

        $regularIncomeKeys = array_keys(array_filter(
            $balances,
            fn ($balance) =>
                $balance['income']->classification !== 'backup'
        ));

        $expenseOptions = [];

        foreach ($items as $expense) {
            $expenseKey = (string) $expense->plan_key;

            $eligible = $this->eligibleIncomes(
                $expense,
                $balances
            );

            // Prefer regular incomes where available.
            $regularEligible = array_values(array_filter(
                $eligible,
                fn ($key) =>
                    in_array($key, $regularIncomeKeys, true)
            ));

            $expenseOptions[$expenseKey] = !empty($regularEligible)
                ? $regularEligible
                : $eligible;
        }

        // A bounded branch-and-bound search.
        // It explores different combinations, including
        // leaving an expense for November.
        $nodesVisited = 0;
        $maxNodes = 100000;

        $search = function (
            int $index,
            array $workingBalances,
            array $workingAssignments,
            int $fundedCents
        ) use (
            &$search,
            &$bestAssignments,
            &$bestFunded,
            &$bestScore,
            &$nodesVisited,
            $maxNodes,
            $items,
            $expenseOptions
        ): void {
            $nodesVisited++;

            if ($nodesVisited > $maxNodes) {
                return;
            }

            if ($index >= count($items)) {
                $score = $this->futureBalanceScore(
                    $workingBalances
                );

                if (
                    $fundedCents > $bestFunded
                    || (
                        $fundedCents === $bestFunded
                        && $score < $bestScore
                    )
                ) {
                    $bestFunded = $fundedCents;
                    $bestScore = $score;
                    $bestAssignments = $workingAssignments;
                }

                return;
            }

            $expense = $items[$index];
            $expenseKey = (string) $expense->plan_key;
            $amountCents = (int) round(
                (float) $expense->amount * 100
            );

            // Try assigning the expense to each eligible income.
            foreach ($expenseOptions[$expenseKey] as $incomeKey) {
                $trial = $workingBalances;

                $this->putExpense(
                    $expense,
                    $incomeKey,
                    $trial
                );

                // Future allocations cannot create negative balances.
                if (
                    $trial[$incomeKey]['remaining'] < -0.000001
                ) {
                    continue;
                }

                $newAssignments = $workingAssignments;
                $newAssignments[$expenseKey] = $incomeKey;

                $search(
                    $index + 1,
                    $trial,
                    $newAssignments,
                    $fundedCents + $amountCents
                );
            }

            // Also try leaving this expense for November.
            $search(
                $index + 1,
                $workingBalances,
                $workingAssignments,
                $fundedCents
            );
        };

        $search(0, $balances, [], 0);

        return $bestAssignments;
    }

    private function futureBalanceScore(
        array $balances
    ): float {
        $regular = array_filter(
            $balances,
            fn ($balance) =>
                $balance['income']->classification !== 'backup'
                && !$balance['income']
                    ->assign_no_expenses_except_tithe
                && (float) $balance['income']->amount > 0
        );

        if (count($regular) < 2) {
            return 0;
        }

        $totalIncome = 0;
        $totalRemaining = 0;

        foreach ($regular as $balance) {
            $totalIncome += (float) $balance['income']->amount;
            $totalRemaining += $balance['remaining'];
        }

        if ($totalIncome <= 0) {
            return 0;
        }

        $target = $totalRemaining / $totalIncome;
        $score = 0;

        foreach ($regular as $balance) {
            $amount = (float) $balance['income']->amount;

            $percentage = $balance['remaining'] / $amount;

            $score += $amount
                * pow($percentage - $target, 2);
        }

        return $score;
    }

    private function assignExpense(
        object $expense,
        string $incomeKey,
        array &$balances,
        array &$assignments
    ): void {
        $this->putExpense(
            $expense,
            $incomeKey,
            $balances
        );

        $assignments[
            (string) $expense->plan_key
        ] = $incomeKey;
    }

    private function putExpense(
        object $expense,
        string $incomeKey,
        array &$balances
    ): void {
        $balances[$incomeKey]['expenses'][
            (string) $expense->plan_key
        ] = $expense;

        $this->recalculate($balances[$incomeKey]);
    }

    private function removeExpense(
        object $expense,
        string $incomeKey,
        array &$balances
    ): void {
        unset(
            $balances[$incomeKey]['expenses'][
                (string) $expense->plan_key
            ]
        );

        $this->recalculate($balances[$incomeKey]);
    }

    private function recalculate(array &$balance): void
    {
        $income = $balance['income'];

        $ordinaryExpenses = 0;
        $manualTithes = 0;

        foreach ($balance['expenses'] as $expense) {
            if ($expense->tithe) {
                $manualTithes += (float) $expense->amount;
            } else {
                $ordinaryExpenses += (float) $expense->amount;
            }
        }

        $titheRequired = $income->tithe
            ? round((float) $income->amount * 0.10, 2)
            : 0;

        $alreadyPaid =
            (float) ($income->paid_manual_tithe ?? 0)
            + (float) ($income->paid_auto_tithe ?? 0);

        $automaticTithe = max(
            0,
            $titheRequired - $alreadyPaid - $manualTithes
        );

        $balance['automatic_tithe'] = round(
            $automaticTithe,
            2
        );

        $balance['remaining'] = round(
            $balance['opening']
                - $ordinaryExpenses
                - $manualTithes
                - $automaticTithe,
            2
        );
    }
}
