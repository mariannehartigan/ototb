<?php

namespace App\Services\ExpensesServices;

use Carbon\Carbon;

class ShortfallAnalysisService
{
    public function analyze(
        array $months,
        float $openingBalance = 0
    ): array {
        $results = [];
        $regularShortfall = 0;
        $totalBackupAvailable = 0;

        $runningRegularBalance = $openingBalance;

        foreach ($months as $month => $data) {
            $regularIncome = 0;
            $backupIncome = 0;
            $protectedIncome = 0;
            $totalExpenses = 0;

            foreach ($data['incomes'] as $income) {
                $amount = (float) $income->amount;

                if ($income->assign_no_expenses_except_tithe ?? false) {
                    $protectedIncome += $amount;
                    continue;
                }

                if (in_array(
                    $income->frequency,
                    ['quarterly', 'yearly', 'onetime'],
                    true
                )) {
                    $backupIncome += $amount;
                } else {
                    $regularIncome += $amount;
                }
            }

            foreach ($data['expenses'] as $expense) {
                $totalExpenses += (float) $expense->amount;
            }

            $monthlyDifference = round(
                $regularIncome - $totalExpenses,
                2
            );

            $runningRegularBalance = round(
                $runningRegularBalance + $monthlyDifference,
                2
            );

            // Only count a new deficit beyond the worst
            // previously observed running balance.
            $regularShortfall = max(
                $regularShortfall,
                -$runningRegularBalance,
                0
            );

            $totalBackupAvailable += $backupIncome;

            $results[$month] = [
                'regular_income' => round($regularIncome, 2),
                'backup_income' => round($backupIncome, 2),
                'protected_income' => round($protectedIncome, 2),
                'expenses' => round($totalExpenses, 2),
                'monthly_difference' => $monthlyDifference,
                'running_regular_balance' => $runningRegularBalance,
            ];
        }

        return [
            'months' => $results,
            'regular_shortfall' => round($regularShortfall, 2),
            'backup_available' => round($totalBackupAvailable, 2),
        ];
    }

    public function analyzeCashflow(
        array $months,
        float $openingBalance = 0
    ): array {
        $events = [];

        foreach ($months as $month => $data) {
            foreach ($data['incomes'] as $income) {
                if ($income->assign_no_expenses_except_tithe ?? false) {
                    continue;
                }

                if (in_array(
                    $income->frequency,
                    ['quarterly', 'yearly', 'onetime'],
                    true
                )) {
                    continue;
                }

                $events[] = [
                    'date' => Carbon::parse($income->date)->toDateString(),
                    'type' => 'income',
                    'amount' => (float) $income->amount,
                ];
            }

            foreach ($data['expenses'] as $expense) {
                $events[] = [
                    'date' => Carbon::parse($expense->date)->toDateString(),
                    'type' => 'expense',
                    'amount' => (float) $expense->amount,
                ];
            }
        }

        usort($events, function ($a, $b) {
            $dateComparison = strcmp($a['date'], $b['date']);

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            // Income received on the due date can be used
            // for that day's expense.
            return ($a['type'] === 'income' ? 0 : 1)
                <=> ($b['type'] === 'income' ? 0 : 1);
        });

        $balance = $openingBalance;
        $lowestBalance = $openingBalance;

        foreach ($events as $event) {
            if ($event['type'] === 'income') {
                $balance += $event['amount'];
            } else {
                $balance -= $event['amount'];
            }

            $lowestBalance = min($lowestBalance, $balance);
        }

        return [
            'ending_balance' => round($balance, 2),
            'lowest_balance' => round($lowestBalance, 2),
            'cashflow_shortfall' => round(
                max(0, -$lowestBalance),
                2
            ),
        ];
    }
}
