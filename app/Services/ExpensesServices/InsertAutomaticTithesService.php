<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class InsertAutomaticTithesService
{
    public function insertAutomaticTithes(Carbon $date, Collection $incomes): void
    {
        $remainingTitheRunningTotal = 0;

        foreach($incomes as $income) {
            $titheExpenses = Expense::where('user_id', Auth::id())
                ->where('tithe', true)
                ->where('automatically_generated_tithe', false)
                ->where('income_id', $income->id)
                ->sum('amount');

            $automaticTithe = $income->amount * 0.1 - $titheExpenses - $remainingTitheRunningTotal;
        
            if ($automaticTithe >= 0) {
        
                $remainingTitheRunningTotal = 0;

                Expense::create([
                    'description' => 'Tithe',
                    'amount' => $automaticTithe,
                    'date' => $income->date,
                    'fixed_or_variable' => null,
                    'manual_or_automatic' => null,
                    'tithe' => true,
                    'automatically_generated_tithe' => true,
                    'account_taken_from' => null,
                    'notes' => null,
                    'income_id' => $income->id,
                    'user_id' => $income->user_id,
                ]);

                $income->remaining -= $automaticTithe;
                $income->save();

            } else {
                $remainingTitheRunningTotal = abs($automaticTithe);
            }
        }

        // if $remainingTitheRunningTotal > 0, take off next automatic tithes
        if($remainingTitheRunningTotal > 0) {

            $automaticTithes = Expense::where('user_id', Auth::id())
                ->where('automatically_generated_tithe', true)
                // going to need to change this later to expense_month_year
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->orderBy('date', 'asc')
                ->get();

            foreach($automaticTithes as $automaticTithe) {
                if($remainingTitheRunningTotal < $automaticTithe->amount) {
                    $automaticTithe->amount -= $remainingTitheRunningTotal;
                    $remainingTitheRunningTotal = 0;
                    $automaticTithe->save();
                } else {
                    $remainingTitheRunningTotal -= $automaticTithe->amount;
                    $automaticTithe->delete();
                }
            }
        }
    }
}