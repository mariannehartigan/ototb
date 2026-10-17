<?php

namespace App\Services\ExpensesServices;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Income;
use App\Models\PlannedExpense;
use App\Models\Expense;

class InitialExpenseAssignmentService
{
    public function push(Carbon $date): void
    {
        $incomes = Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();



        // foreach($incomes as $income) {
        //     Expense::create([
        //         'description' => 'tithe',
        //         'amount' => $income->amount * 0.1,
        //         'date' => $income->date,
        //         'fixed_or_variable' => null,
        //         'manual_or_automatic' => null,
        //         'tithe' => true,
        //         'account_taken_from' => null,
        //         'notes' => null,
        //         'income_id' => $income->id,
        //         'user_id' => $income->user_id,
        //     ]);
        // }
    }
}