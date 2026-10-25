<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class InsertExpensesService
{
    public function insertExpenses(Carbon $date, Collection $incomes, Collection $expenses): void
    {
        foreach($expenses as $expense) {
        
            $detail = $expense->plannedExpenseDetails->last();
            $amount = $detail->amount;

            // if there is already a planned_income_id specified
            if ($detail->planned_income_id !== null) {

                $income = $incomes->first(
                    fn ($item) => $item->planned_income_id == $detail->planned_income_id
                );
/*                 Expense::create([
                    'date' => $date->copy()->day(min($detail->day_due_for_monthly, $date->daysInMonth)),
                    'description' => $expense->description,
                    'amount' => $detail->amount,
                    'fixed_or_variable' => $detail->fixed_or_variable,
                    'manual_or_automatic' => $detail->manual_or_automatic,
                    'tithe' => $expense->tithe,
                    'automatic_payment' => $detail->manual_or_automatic === 'automatic',
                    'account_taken_from' => $detail->account_taken_from,
                    'notes' => $expense->notes,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(), */

/*                     'date' => $date,
                    'description' => $expense->description,
                    'tithe' => $expense->tithe,
                    'amount' => $expense->amount,
                    'automatic_payment' => $expense->manual_or_automatic === 'automatic',
                    'account_taken_from' => $expense->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(), */
                // ]);

/*                 $income->remaining -= $expense->amount;
                $income->save(); */

            } else {

                $income = $incomes
                    ->filter(fn ($income) => $income->date <= $date)
                    ->sortBy(function ($income) use ($date, $incomes, $expense) {

                        $newRemaining = $income->remaining - $expense->amount;

                        $balances = $incomes
                            ->filter(fn ($i) => $i->date <= $date)
                            ->map(function ($i) use ($income, $newRemaining) {
                                return $i->id === $income->id
                                    ? $newRemaining
                                    : $i->remaining;
                            });

                        return [
                            $balances->max() - $balances->min(),
                            abs($newRemaining),
                            -$income->date->timestamp,
                        ];
                    })
                    ->first();

                Expense::create([
                    'date' => $date->copy()->day(min($detail->day_due_for_monthly, $date->daysInMonth)),
                    'description' => $expense->description,
                    'amount' => $amount,
                    'fixed_or_variable' => $detail->fixed_or_variable,
                    'manual_or_automatic' => $detail->manual_or_automatic,
                    'tithe' => $expense->tithe,
                    'automatic_payment' => $detail->manual_or_automatic === 'automatic',
                    'account_taken_from' => $detail->account_taken_from,
                    'notes' => $expense->notes,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                
/*                     'date' => $date,
                    'description' => $expense->description,
                    'tithe' => $expense->tithe,
                    'amount' => $expense->amount,
                    'automatic_payment' => $expense->manual_or_automatic === 'automatic',
                    'account_taken_from' => $expense->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(), */
                ]);

                $income->remaining -= $expense->amount;
                $income->save();
            }            
        }
    }
}