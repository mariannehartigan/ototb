<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;

class AllocateExpensesService
{
    public function allocateExpense(PlannedExpense $plannedExpense, Collection $incomes, Carbon $date): void {

            $endOfMonth = $date->copy()->endOfMonth();

            $detail = $plannedExpense->plannedExpenseDetails()
                ->whereDate('effective_date', '<=', $endOfMonth)
                ->orderByDesc('effective_date')
                ->first();

            // if there is already a planned_income_id specified
            if ($detail->planned_income_id !== null) {
                $income = $incomes->first(
                    fn ($item) => $item->planned_income_id == $detail->planned_income_id
                );
                \App\Models\Expense::create([
                    'date' => $date,
                    'description' => $plannedExpense->description,
                    'tithe' => $plannedExpense->tithe,
                    'amount' => $detail->amount,
                    'automatic_payment' => $detail->manual_or_automatic === 'automatic',
                    'account_taken_from' => $detail->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                ]);
                $income->remaining -= $detail->amount;
                $income->save();
            } else {
                $income = $incomes
                    ->filter(fn ($income) => $income->date <= $date)
                    ->sortBy(function ($income) use ($date, $incomes, $detail) {

                        $newRemaining = $income->remaining - $detail->amount;

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

                if (!$income) {
                    return;
                }

                \App\Models\Expense::create([
                    'date' => $date,
                    'description' => $plannedExpense->description,
                    'tithe' => $plannedExpense->tithe,
                    'amount' => $detail->amount,
                    'automatic_payment' => $detail->manual_or_automatic === 'automatic',
                    'account_taken_from' => $detail->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                ]);

                $income->remaining -= $detail->amount;
                $income->save();
            }
    }
}
