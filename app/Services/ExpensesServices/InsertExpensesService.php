<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class InsertExpensesService
{
    public function insertExpenses(Carbon $date, Collection $incomes, Collection $expenses): void {

/*         $totalIncomes = $incomes->sum('amount');
        $totalExpenses = $expenses->sum(function ($expense) {
            return $expense->plannedExpenseDetails->last()->amount;
        }); */
        
        foreach ($expenses as $expense) {

            $detail = $expense->plannedExpenseDetails->last();
            $amount = $detail->amount;

            // If planned income is specified
            if ($detail->planned_income_id !== null) {
                $income = $incomes->first(
                    fn ($item) =>
                        $item->planned_income_id == $detail->planned_income_id
                );

                if ($income !== null) {
                    $this->insertExpense($date, $expense, $detail, $income);
                }

            } else {

                // find first cumulative income that covers all previous expenses
                $starterIncome = $this->findFirstIncomeToCoverExpenses(
                    $date,
                    $detail,
                    $incomes
                );

                

/*                 if ($income !== null) {
                    $this->insertExpense($date, $expense, $detail, $income);
                } */
            
            }
        }
    }

    private function insertExpense(
        Carbon $date,
        $expense,
        $detail,
        $income
    ): void {

        $amount = $detail->amount;

        Expense::create([
            'date' => $date->copy()->day(
                min($detail->day_due_for_monthly, $date->daysInMonth)
            ),
            'month_year' => $date->format('Y-m'),
            'description' => $expense->description,
            'amount' => $amount,
            'fixed_or_variable' => $detail->fixed_or_variable,
            'manual_or_automatic' => $detail->manual_or_automatic,
            'tithe' => $expense->tithe,
            'automatic_payment' =>
                $detail->manual_or_automatic === 'automatic',
            'account_taken_from' => $detail->account_taken_from,
            'notes' => $expense->notes,
            'income_id' => $income->id,
            'user_id' => Auth::id(),
        ]);

        $income->remaining -= $amount;
        $income->save();
    }

    private function findFirstIncomeToCoverExpenses(
        Carbon $date, $detail, Collection $incomes) {
        $amount = $detail->amount;

        $dueDate = $date->copy()->day(
            min($detail->day_due_for_monthly, $date->daysInMonth)
        );

        // Try incomes from earliest to latest.
        $sortedIncomes = $incomes
            ->filter(fn ($income) => $income->date <= $dueDate)
            ->sortBy('date');

        foreach ($sortedIncomes as $candidate) {

            // Calculate the running balance if this expense
            // were assigned to the candidate income.
            $runningIncome = 0;
            $runningExpenses = 0;
            $valid = true;

            $incomesUpToDueDate = $incomes
                ->filter(fn ($income) => $income->date <= $dueDate)
                ->sortBy('date');

            foreach ($incomesUpToDueDate as $income) {

                $runningIncome += $income->amount;

                // Existing expenses assigned to this income.
                $runningExpenses +=
                    $income->amount - $income->remaining;

                // Include this expense at the candidate income.
                if ($income->id === $candidate->id) {
                    $runningExpenses += $amount;
                }

                if ($runningExpenses > $runningIncome) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                return $candidate;
            }
        }

        return null;

    }
}