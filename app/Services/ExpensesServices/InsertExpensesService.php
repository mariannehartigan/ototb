<?php

namespace App\Services\ExpensesServices;

use App\Models\Expense;
use App\Models\PlannedExpense;
use App\Models\PlannedExpenseDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InsertExpensesService
{
    public function insertExpenses(Carbon $date): void
    {
        $this->generateExpenses(
            $date,
            function (
                PlannedExpense $plannedExpense,
                PlannedExpenseDetail $detail,
                Carbon $dueDate
            ) {
                $this->createExpense(
                    $plannedExpense,
                    $detail,
                    $dueDate
                );
            }
        );
    }

    public function projectExpenses(Carbon $date): Collection
    {
        $projections = collect();

        $this->generateExpenses(
            $date,
            function (
                PlannedExpense $plannedExpense,
                PlannedExpenseDetail $detail,
                Carbon $dueDate
            ) use ($projections) {

                $expense = new Expense([
                    'planned_expense_id' => $plannedExpense->id,
                    'date' => $dueDate->toDateString(),
                    'month_year' => $dueDate->copy()
                        ->startOfMonth()
                        ->toDateString(),
                    'description' => $plannedExpense->description,
                    'amount' => $detail->amount,
                    'fixed_or_variable' => $detail->fixed_or_variable,
                    'manual_or_automatic' => $detail->manual_or_automatic,
                    'account_taken_from' => $detail->account_taken_from,
                    'tithe' => $plannedExpense->tithe,
                    'automatically_generated_tithe' => false,
                    'paid' => false,
                    'notes' => $plannedExpense->notes,
                    'income_id' => null,
                    'user_id' => Auth::id(),
                ]);

                // Used by DistributeExpensesService.
                $expense->planned_income_id =
                    $detail->planned_income_id;

                // This expense has not been saved.
                $expense->is_projection = true;

                $projections->push($expense);
            }
        );

        return $projections;
    }

    private function generateExpenses(
        Carbon $date,
        callable $callback
    ): void {

        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $plannedExpenses = PlannedExpense::where(
            'user_id',
            Auth::id()
        )
            ->where(function ($query) use ($monthEnd) {
                $query->whereDate('start_date', '<=', $monthEnd)
                    ->orWhereNull('start_date');
            })
            ->where(function ($query) use ($monthStart) {
                $query->whereDate('end_date', '>=', $monthStart)
                    ->orWhereNull('end_date');
            })
            ->with('plannedExpenseDetails')
            ->get();

        foreach ($plannedExpenses as $plannedExpense) {

            $details = $plannedExpense->plannedExpenseDetails;

            if ($details->isEmpty()) {
                continue;
            }

            $startDate = $plannedExpense->start_date
                ? Carbon::parse($plannedExpense->start_date)
                : $monthStart->copy();

            $endDate = $plannedExpense->end_date
                ? Carbon::parse($plannedExpense->end_date)
                : $monthEnd->copy();

            $firstDate = $startDate->greaterThan($monthStart)
                ? $startDate->copy()
                : $monthStart->copy();

            $lastDate = $endDate->lessThan($monthEnd)
                ? $endDate->copy()
                : $monthEnd->copy();

            if ($firstDate->greaterThan($lastDate)) {
                continue;
            }

            $this->generateExpenseDates(
                $plannedExpense,
                $firstDate,
                $lastDate,
                $callback
            );
        }
    }

    private function generateExpenseDates(
        PlannedExpense $plannedExpense,
        Carbon $firstDate,
        Carbon $lastDate,
        callable $callback
    ): void {

        $details = $plannedExpense->plannedExpenseDetails
            ->sortBy([
                ['effective_date', 'asc'],
                ['id', 'asc'],
            ]);

        $date = $firstDate->copy();

        while ($date->lessThanOrEqualTo($lastDate)) {

            // Find the details effective on this date.
            $applicableDetails = $details->filter(
                function ($detail) use ($date) {
                    return $detail->effective_date === null
                        || Carbon::parse($detail->effective_date)
                            ->lessThanOrEqualTo($date);
                }
            );

            if ($applicableDetails->isNotEmpty()) {

                // Use only the latest set of details.
                $latestEffectiveDate = $applicableDetails
                    ->max('effective_date');

                $currentDetails = $applicableDetails->filter(
                    fn ($detail) =>
                        $detail->effective_date == $latestEffectiveDate
                );

                foreach ($currentDetails as $detail) {

                    if ($this->isDueDate($date, $detail)) {

                        $callback(
                            $plannedExpense,
                            $detail,
                            $date->copy()
                        );
                    }
                }
            }

            $date->addDay();
        }
    }

    private function isDueDate(
        Carbon $date,
        PlannedExpenseDetail $detail
    ): bool {

        switch ($detail->frequency) {

            case 'weekly':
                if (!$detail->day_of_week_for_weekly_fixed) {
                    return false;
                }

                return $date->englishDayOfWeek ===
                    $detail->day_of_week_for_weekly_fixed;

            case 'biweekly':
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'biweekly'
                );

            case 'monthly':
                if (!$detail->day_due_for_monthly) {
                    return false;
                }

                return $date->day === min(
                    (int) $detail->day_due_for_monthly,
                    $date->daysInMonth
                );

            case 'quarterly':
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'quarterly'
                );

            case 'yearly':
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'yearly'
                );

            case 'onetime':
                if (!$detail->due_date_for_biweekly_quarterly_yearly_onetime) {
                    return false;
                }

                return $date->isSameDay(
                    Carbon::parse(
                        $detail->due_date_for_biweekly_quarterly_yearly_onetime
                    )
                );

            default:
                return false;
        }
    }

    private function matchesInterval(
        Carbon $date,
        PlannedExpenseDetail $detail,
        string $frequency
    ): bool {

        if (!$detail->due_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->due_date_for_biweekly_quarterly_yearly_onetime
        )->startOfDay();

        if ($date->lessThan($anchor)) {
            return false;
        }

        if ($frequency === 'biweekly') {
            return (int) $anchor->diffInDays($date) % 14 === 0;
        }

        if ($frequency === 'quarterly') {

            $months = (
                ($date->year - $anchor->year) * 12
                + $date->month - $anchor->month
            );

            if ($months < 0 || $months % 3 !== 0) {
                return false;
            }

            return $date->day === min(
                $anchor->day,
                $date->daysInMonth
            );
        }

        if ($frequency === 'yearly') {
            return $date->month === $anchor->month
                && $date->day === min(
                    $anchor->day,
                    $date->daysInMonth
                );
        }

        return false;
    }

    private function createExpense(
        PlannedExpense $plannedExpense,
        PlannedExpenseDetail $detail,
        Carbon $date
    ): void {

        // Do not generate an existing occurrence again.
        $exists = Expense::where('user_id', Auth::id())
            ->where('planned_expense_id', $plannedExpense->id)
            ->whereDate('date', $date->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        Expense::create([
            'planned_expense_id' => $plannedExpense->id,
            'date' => $date->toDateString(),
            'month_year' => $date->copy()
                ->startOfMonth()
                ->toDateString(),
            'description' => $plannedExpense->description,
            'amount' => $detail->amount,
            'fixed_or_variable' => $detail->fixed_or_variable,
            'manual_or_automatic' => $detail->manual_or_automatic,
            'account_taken_from' => $detail->account_taken_from,
            'tithe' => $plannedExpense->tithe,
            'automatically_generated_tithe' => false,
            'paid' => false,
            'notes' => $plannedExpense->notes,
            'income_id' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
