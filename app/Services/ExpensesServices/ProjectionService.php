<?php

namespace App\Services\ExpensesServices;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectionService
{
    public function buildProjection(
        Collection $incomes,
        Collection $expenses,
        array $fundingChoices = []
    ): array {
        $months = [];

        foreach ($incomes as $income) {
            $month = Carbon::parse($income->date)->format('Y-m');

            $months[$month] ??= [
                'incomes' => [],
                'expenses' => [],
            ];

            $months[$month]['incomes'][] = $income;
        }

        foreach ($expenses as $expense) {
            $dueDate = Carbon::parse($expense->date);

            $followingMonth = $fundingChoices[
                $expense->planned_expense_id
            ] ?? (bool) ($expense->assign_to_following_month ?? false);

            $fundingMonth = $followingMonth
                ? $dueDate->copy()->subMonthNoOverflow()->format('Y-m')
                : $dueDate->format('Y-m');

            $months[$fundingMonth] ??= [
                'incomes' => [],
                'expenses' => [],
            ];

            // Keep the original due date.
            $months[$fundingMonth]['expenses'][] = (object) [
                'id' => $expense->id,
                'planned_expense_id' => $expense->planned_expense_id,
                'planned_income_id' => $expense->planned_income_id ?? null,
                'date' => $dueDate->toDateString(),
                'funding_month' => $fundingMonth,
                'amount' => (float) $expense->amount,
                'tithe' => (bool) ($expense->tithe ?? false),
            ];
        }

        ksort($months);

        return $months;
    }
}
