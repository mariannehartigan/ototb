<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Models\PlannedExpenseDetail;
use App\Models\Expense;

class InsertExpenseNoIncomeIdService
{
/*     public function insertExpenseNoIncomeId(Carbon $date, PlannedExpense $expense): void
    {
        Expense::create([
            'description' => 'tithe',
            'amount' => $expense->amount,
            'date' => $date,
            'fixed_or_variable' => null,
            'manual_or_automatic' => null,
            'tithe' => true,
            'account_taken_from' => null,
            'notes' => null,
            'income_id' => null,
            'user_id' => Auth::id(),
        ]);
    } */
    public function insertExpenseNoIncomeId(
        Carbon $date,
        PlannedExpense $expense,
        PlannedExpenseDetail $detail
    ): void {
        Expense::create([
            'description' => $expense->description,
            'amount' => $detail->amount,
            'date' => $date,
            'fixed_or_variable' => $detail->fixed_or_variable,
            'manual_or_automatic' => $detail->manual_or_automatic,
            'tithe' => true,
            'account_taken_from' => $detail->account_taken_from,
            'notes' => $expense->notes,
            'income_id' => null,
            'user_id' => Auth::id(),
        ]);
    }
}