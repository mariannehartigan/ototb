<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class AdjustTithesService
{
    public function adjustTithes(Carbon $date, Collection $incomes): void
    {
        $titheExpenses = Expense::where('user_id', Auth::id())
            ->where('tithe', true)
            ->where('automatically_generated_tithe', false)
            ->where('paid', false)
            ->orderBy('date')
            ->get();

        $automaticallyGeneratedTithes = Expense::where('user_id', Auth::id())
            ->where('automatically_generated_tithe', true)
            ->where('paid', false)
            ->orderBy('date')
            ->get();

        foreach($titheExpenses as $titheExpense) {
            $automaticallyGeneratedTithes = Expense::where('user_id', Auth::id())
                ->where('income_id', $titheExpense->income_id)
                ->where('automatically_generated_tithe', true)
                ->where('paid', false)
                ->orderBy('date')
                ->get();
            foreach($automaticallyGeneratedTithes as $automaticallyGeneratedTithe) {
                $difference = $titheExpense->amount - $automaticallyGeneratedTithe->amount;
                while ($difference > 0) {
                    if ($automaticallyGeneratedTithe->amount >= $titheExpense->amount) {
                        $automaticallyGeneratedTithe->amount =
                            $automaticallyGeneratedTithe->amount - $titheExpense->amount;
                    } else {
                        $automaticallyGeneratedTithe->delete();
                    }
                }
            }
        }
    }
}

/* 
- if generated.amount >= expense.amount
	subtract expense from generated => DONE
- if generated.amount < expense.amount
	delete generated 

    */