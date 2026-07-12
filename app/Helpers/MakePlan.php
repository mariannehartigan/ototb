<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

function makePlan(Carbon $date)
{
    // POPULATE INCOME TABLE

    $month = $date->month;
    $monthName = $date->format('F');
    $year = $date->year;

    $incomes = App\Models\PlannedIncome::where('user_id', Auth::id())->get();

    foreach ($incomes as $income) {
        $day = min($income->day_deposited, $date->daysInMonth); // constrain to last day of month instead of 31st
        App\Models\Income::create([
            'date' => Carbon::create($year, $month, $day),
            'description' => $income->description,
            'amount' => $income->amount,
            'remaining' => $income->amount,
            'user_id' => $income->user_id,
        ]);
    }


    // POPULATE EXPENSE TABLE

    $expenses = App\Models\PlannedExpense::where('user_id', Auth::id())->get();

    foreach ($expenses as $expense) {

        $firstIncomeInMonth = App\Models\Income::where('user_id', Auth::id())->orderBy('date')->first();
        
        $expenseDate = Carbon::create(
            $year,
            $month,
            min($expense->day_due, $date->daysInMonth)
        );

        if ($expense->day_due < $firstIncomeInMonth->date->day) {
            $expenseDate->addMonth();
        }

        $incomes = App\Models\Income::orderBy('remaining', 'desc')->get();
        foreach ($incomes as $income) {
            if ($income->day <= $expense['day_due']) {
                App\Models\Expense::create([
                    'date' => $expenseDate,
                    'description' => $expense['description'],
                    'amount' => $expense['amount'],
                    'automatic_payment' => $expense['automatic_payment'],
                    'account_taken_from' => $expense['account_taken_from'],
                    'income_id' => $income->id,
                    'user_id' => $income->user_id,
                ]);
                $income->remaining -= $expense['amount'];
                $income->save(); 
                break;
            }
        }
    }    
}