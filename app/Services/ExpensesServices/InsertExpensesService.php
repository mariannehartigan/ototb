<?php

namespace App\Services\ExpensesServices;

use App\Models\Expense;
use App\Models\PlannedExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InsertExpensesService
{
    public function insertExpenses(Carbon $date): void
    {
        $month = $date->copy()->startOfMonth();

        $plannedExpenses = PlannedExpense::where('user_id', Auth::id())
            ->with('plannedExpenseDetails')
            ->get();

        foreach ($plannedExpenses as $plannedExpense) {

            $details = $plannedExpense->plannedExpenseDetails;

            // Only monthly expenses are handled here.
            // Other frequencies will need their own generators.
            $monthlyDetails = $details->where('frequency', 'monthly');

            if ($monthlyDetails->isEmpty()) {
                continue;
            }

            // Find the applicable monthly detail for this month.
            // Use the actual due date to determine which detail
            // was in effect when the payment was due.
            $detail = $monthlyDetails
                ->sortBy([
                    ['effective_date', 'asc'],
                    ['id', 'asc'],
                ])
                ->reverse()
                ->first(function ($detail) use ($month) {

                    if (!$detail->day_due_for_monthly) {
                        return false;
                    }

                    $dueDate = $month->copy()->day(
                        min(
                            (int) $detail->day_due_for_monthly,
                            $month->daysInMonth
                        )
                    );

                    return $detail->effective_date === null
                        || Carbon::parse($detail->effective_date)
                            ->lte($dueDate);
                });

            if (!$detail) {
                continue;
            }

            $dueDate = $month->copy()->day(
                min(
                    (int) $detail->day_due_for_monthly,
                    $month->daysInMonth
                )
            );

            // Respect planned expense start and end dates.
            if (
                $plannedExpense->start_date
                && $dueDate->lt(Carbon::parse($plannedExpense->start_date))
            ) {
                continue;
            }

            if (
                $plannedExpense->end_date
                && $dueDate->gt(Carbon::parse($plannedExpense->end_date))
            ) {
                continue;
            }

            // Don't generate the same planned expense twice
            // for the same actual due date.
            $exists = Expense::where('user_id', Auth::id())
                ->where('planned_expense_id', $plannedExpense->id)
                ->whereDate('date', $dueDate->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            Expense::create([
                'planned_expense_id' => $plannedExpense->id,
                'date' => $dueDate->toDateString(),
                'month_year' => $month->toDateString(),
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
}
