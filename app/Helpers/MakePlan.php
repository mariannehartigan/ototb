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

    // INSERT FUTURE BUDGET INTO BUDGET
    $settings = App\Models\Setting::where('user_id', Auth::id())->first();

    if ($settings && $settings->budget_type === 'averaged') {

        $budgetConversionFactor = (364.75/(7*12)-4)/4;
        // to find the extra weekly amount that has to be saved for 5-Fridays
        // yields 0.0855654761904760
        // 4.3423 weeks per month, 364.75/(7*12)
        // subtract 4, so have to account for the extra 0.3423 weeks in a month
        // divide by 4, each week has to account for 0.3423/4 = 0.08557 (there are 4 budgets in a month)

        $futureBudget = App\Models\Budget::where('frequency', 'weekly')->sum('amount') * $budgetConversionFactor;

        App\Models\Budget::firstOrCreate(
            [
                'description' => 'Future budget',
                'frequency' => 'weekly',
                'user_id' => Auth::id(),
            ], [
                'amount' => $futureBudget,
            ]
        );

    // INSERT WEEKLY BUDGETS INTO EXPENSES
        // find day_deposited first budget will be assigned to
        $totalPlannedIncomes = App\Models\PlannedIncome::where('user_id', Auth::id())->sum('amount');
        $orderedPlannedIncomes = App\Models\PlannedIncome::orderBy('day_deposited')->get();
        $cumulative = 0;

        $firstBudgetDayOfMonth = null;
        foreach ($orderedPlannedIncomes as $plannedIncome) {
            $cumulative += $plannedIncome->amount;
            if ($cumulative >= $totalPlannedIncomes/4.3423) {
                $firstBudgetDayOfMonth = $plannedIncome->day_deposited;
                break; // no need to continue
            }
        }
        
        // convert $firstBudgetDayOfMonth to $budgetDayOfWeek after paydate
        $budgetDayOfWeek = App\Models\Setting::where('user_id', Auth::id())->value('budget_day_of_week');
        $firstBudgetDate = Carbon::create($year, $month, $firstBudgetDayOfMonth);

        while ($firstBudgetDate->format('l') !== $budgetDayOfWeek) {
            $firstBudgetDate->addDay();
        }
            dd($firstBudgetDate);

$budgetDate = $date->day;




        $weeklyBudget = App\Models\Budget::where('user_id', Auth::id())->where('frequency', 'weekly')->sum('amount');







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