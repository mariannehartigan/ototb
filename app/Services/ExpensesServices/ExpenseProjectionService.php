<?php

namespace App\Services\ExpensesServices;

use App\Models\PlannedExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ExpenseProjectionService
{
    public function project(
        Carbon $start,
        int $months = 24,
        array $fundingChoices = []
    ): Collection {

        $startDate = $start->copy()->startOfMonth();

        $endDate = $startDate->copy()
            ->addMonthsNoOverflow($months)
            ->subDay();

        $plannedExpenses = PlannedExpense::where('user_id', Auth::id())
            ->with(['plannedExpenseDetails' => function ($query) use ($endDate) {
                $query->where(function ($query) use ($endDate) {
                    $query->whereDate('effective_date', '<=', $endDate)
                        ->orWhereNull('effective_date');
                })
                ->orderBy('effective_date')
                ->orderBy('id');
            }])
            ->get();

        $occurrences = collect();

        foreach ($plannedExpenses as $plannedExpense) {

            $details = $plannedExpense->plannedExpenseDetails;

            if ($details->isEmpty()) {
                continue;
            }

            $date = $startDate->copy();

            while ($date->lte($endDate)) {

                if (!$this->isActive($plannedExpense, $date)) {
                    $date->addDay();
                    continue;
                }

                // Find the latest detail effective on this due date.
                $detail = $details
                    ->filter(fn ($item) =>
                        $item->effective_date === null
                        || Carbon::parse($item->effective_date)->lte($date)
                    )
                    ->last();

                if (!$detail || !$this->isDueDate($date, $detail)) {
                    $date->addDay();
                    continue;
                }

                // Use a temporary algorithm choice if provided.
                // Otherwise use the value stored in the database.
                $followingMonth = array_key_exists(
                    $plannedExpense->id,
                    $fundingChoices
                )
                    ? (bool) $fundingChoices[$plannedExpense->id]
                    : (bool) $detail->assign_to_following_month;

                // Keep the actual due date unchanged.
                // Only the funding month is adjusted.
                $fundingMonth = $followingMonth
                    ? $date->copy()
                        ->subMonthNoOverflow()
                        ->format('Y-m')
                    : $date->format('Y-m');

                $occurrences->push((object) [
                    'id' => 'expense-'
                        . $plannedExpense->id
                        . '-'
                        . $date->format('Y-m-d'),

                    'planned_expense_id' => $plannedExpense->id,
                    'planned_expense_detail_id' => $detail->id,
                    'planned_income_id' => $detail->planned_income_id,

                    'description' => $plannedExpense->description,
                    'date' => $date->toDateString(),
                    'amount' => (float) $detail->amount,
                    'frequency' => $detail->frequency,

                    'fixed_or_variable' => $detail->fixed_or_variable,
                    'manual_or_automatic' => $detail->manual_or_automatic,
                    'account_taken_from' => $detail->account_taken_from,

                    'is_five_budget_month_savings' =>
                        (bool) $detail->is_five_budget_month_savings,

                    'tithe' => (bool) $plannedExpense->tithe,

                    'assign_to_following_month' => $followingMonth,
                    'funding_month' => $fundingMonth,
                ]);

                $date->addDay();
            }
        }

        return $occurrences
            ->sortBy([
                ['date', 'asc'],
                ['planned_expense_id', 'asc'],
            ])
            ->values();
    }

    private function isActive(
        PlannedExpense $expense,
        Carbon $date
    ): bool {

        if (
            $expense->start_date &&
            $date->lt(Carbon::parse($expense->start_date))
        ) {
            return false;
        }

        if (
            $expense->end_date &&
            $date->gt(Carbon::parse($expense->end_date))
        ) {
            return false;
        }

        return true;
    }

    private function isDueDate(Carbon $date, $detail): bool {

        switch ($detail->frequency) {

            case 'weekly':
                return $date->englishDayOfWeek ===
                    $detail->day_of_week_for_weekly_biweekly;

            case 'biweekly':
                return $this->matchesBiweekly($date, $detail);

            case 'monthly':
                if (!$detail->day_due_for_monthly) {
                    return false;
                }

                return $date->day === min(
                    (int) $detail->day_due_for_monthly,
                    $date->daysInMonth
                );

            case 'quarterly':
                return $this->matchesMonthlyInterval(
                    $date,
                    $detail,
                    3
                );

            case 'yearly':
                return $this->matchesMonthlyInterval(
                    $date,
                    $detail,
                    12
                );

            case 'onetime':
                return $detail->due_date_for_biweekly_quarterly_yearly_onetime
                    && $date->isSameDay(
                        Carbon::parse(
                            $detail->due_date_for_biweekly_quarterly_yearly_onetime
                        )
                    );

            default:
                return false;
        }
    }

    private function matchesBiweekly(
        Carbon $date,
        $detail
    ): bool {

        if (!$detail->due_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->due_date_for_biweekly_quarterly_yearly_onetime
        );

        if ($date->lt($anchor)) {
            return false;
        }

        return $anchor->diffInDays($date) % 14 === 0;
    }

    private function matchesMonthlyInterval(
        Carbon $date,
        $detail,
        int $interval
    ): bool {

        if (!$detail->due_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->due_date_for_biweekly_quarterly_yearly_onetime
        );

        if ($date->lt($anchor)) {
            return false;
        }

        $months = ($date->year - $anchor->year) * 12
            + ($date->month - $anchor->month);

        if ($months < 0 || $months % $interval !== 0) {
            return false;
        }

        return $date->day === min(
            $anchor->day,
            $date->daysInMonth
        );
    }
}
