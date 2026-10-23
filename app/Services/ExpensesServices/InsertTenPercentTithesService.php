<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Models\Expense;
use App\Services\ExpensesServices\AllocateExpensesToIncomeIdService;

class InsertTenPercentTithesService
{
    public function insertTenPercentTithes(Carbon $date, Collection $incomes): void
    {
        foreach($incomes as $income) {
            Expense::create([
                'description' => 'Tithe',
                'amount' => $income->amount * 0.1,
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
        }
    }
}