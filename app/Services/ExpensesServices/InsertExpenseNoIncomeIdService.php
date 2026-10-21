<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Expense;

class InsertExpenseNoIncomeIdService
{
    public function insertExpenseNoIncomeId(Carbon $date, $expense): void
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
    }
}