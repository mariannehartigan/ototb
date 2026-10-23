<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class AllocateExpensesToIncomeIdService
{
    public function allocateExpensesToIncomeId(Carbon $date, Collection $incomes, Expense $expense): void {

            $endOfMonth = $date->copy()->endOfMonth();

            // already assigned an income_id
            if ($expense->automatically_generated_tithe) {
                return;
            }

            // if there is already a planned_income_id specified
            if ($expense->planned_income_id !== null) {
                $income = $incomes->first(
                    fn ($item) => $item->planned_income_id == $expense->planned_income_id
                );
/*                 \App\Models\Expense::create([
                    'date' => $date,
                    'description' => $expense->description,
                    'tithe' => $expense->tithe,
                    'amount' => $expense->amount,
                    'automatic_payment' => $expense->manual_or_automatic === 'automatic',
                    'account_taken_from' => $expense->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                ]); */

                $expense->income_id = $income->id;
                $expense->save();

                $income->remaining -= $expense->amount;
                $income->save();
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

                $expense->income_id = $income->id;
                $expense->save();

/*                 \App\Models\Expense::create([
                    'date' => $date,
                    'description' => $expense->description,
                    'tithe' => $expense->tithe,
                    'amount' => $expense->amount,
                    'automatic_payment' => $expense->manual_or_automatic === 'automatic',
                    'account_taken_from' => $expense->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                ]); */

                $income->remaining -= $expense->amount;
                $income->save();
            }
    }
}
