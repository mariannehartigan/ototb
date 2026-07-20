<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

function makePlan(Carbon $date)
{
    // POPULATE INCOME TABLE FROM PLANNED INCOMES
    $incomes = App\Models\PlannedIncome::where('user_id', Auth::id())->get();

    foreach ($incomes as $income) {
        $day = min($income->day_deposited, $date->daysInMonth); // constrain to last day of month instead of 31st
        App\Models\Income::create([
            'date' => Carbon::create($date->year, $date->month, $day),
            'description' => $income->description,
            'amount' => $income->amount,
            'remaining' => $income->amount,
            'user_id' => Auth::id(),
        ]);
    }
    
    // 5-BUDGET
    $settings = App\Models\Setting::where('user_id', Auth::id())->first();
    if ($settings && $settings->budget_type === 'averaged') {

        // CREATE WEEKLY SAVINGS
        $budgetConversionFactor = (364.75/(7*12)-4)/4;
        // to find the extra weekly amount that has to be saved for 5-Fridays
        // yields 0.0855654761904760
        // 4.3423 weeks per month, 364.75/(7*12)
        // subtract 4, so have to account for the extra 0.3423 weeks in a month
        // divide by 4, each week has to account for 0.3423/4 = 0.08557 (there are 4 budgets in a month)

        $futureBudget = App\Models\Budget::where('frequency', 'weekly')->sum('amount') * $budgetConversionFactor;
        $budgetDayOfWeek = App\Models\Setting::where('user_id', Auth::id())->value('budget_day_of_week');

        App\Models\Budget::firstOrCreate(
            [
                'description' => '5-'.$budgetDayOfWeek,
                'frequency' => 'weekly',
                'user_id' => Auth::id(),
            ], [
                'amount' => $futureBudget,
                'user_id' => Auth::id(),
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
        
        // get date of first budget
        $budgetDayOfWeek = App\Models\Setting::where('user_id', Auth::id())->value('budget_day_of_week');
        $firstBudgetDate = Carbon::create($date->year, $date->month, $firstBudgetDayOfMonth);

        while ($firstBudgetDate->format('l') !== $budgetDayOfWeek) {
            $firstBudgetDate->addDay();
        }

        // find out how many budgets would be in the lead month
        $arrayOfBudgetDates = [];
        $currentDateIteration = $firstBudgetDate->copy()->startOfMonth();

        while ($date->month === $currentDateIteration->month) {
            if ($currentDateIteration->format('l') === $budgetDayOfWeek) {
                $arrayOfBudgetDates[] = $currentDateIteration->day;
            }
            $currentDateIteration->addDay();
        }
        $numberOfBudgetDays = count($arrayOfBudgetDates);

        // insert weekly budgets into expenses table
        for ($i = 0; $i < $numberOfBudgetDays; $i++) {
            $budgetDate = $firstBudgetDate->copy()->addDays($i * 7);

            App\Models\Expense::create([
                'description' => 'Budget ' . $budgetDate->format('F j'),
                'amount' => App\Models\Budget::where('user_id', Auth::id())
                    ->where('frequency', 'weekly')
                    ->sum('amount'),
                'date' => $budgetDate,
                'user_id' => Auth::id(),
            ]);
        }
        // potential problem with duplicate dates for budget allocation

    } // end if weekly budget === 'averaged'

    // POPULATE EXPENSE TABLE
    $plannedExpenses = App\Models\PlannedExpense::where('user_id', Auth::id())->get();

    $monthlyBudgets = App\Models\Budget::where('user_id', Auth::id())
        ->where('frequency', 'monthly')
        ->get()
        ->map(function ($budget) {
            $budget->date = Carbon::create(
                now()->year,
                now()->month,
                $budget->date_available
            );
        $budget->day_due = $budget->date_available;
            $budget->automatic_payment = false;
            $budget->account_taken_from = 'debit';
            $budget->notes = null;
            $budget->tithe = false;
            return $budget;
        });

    $combinedExpenses = $plannedExpenses->concat($monthlyBudgets);

    foreach ($combinedExpenses as $expense) {

        $firstIncomeInMonth = App\Models\Income::where('user_id', Auth::id())->orderBy('date')->first();
        
        $expenseDate = Carbon::create(
            $date->year,
            $date->month,
            min($expense->day_due, $date->daysInMonth)
        );

        if ($expense->day_due < $firstIncomeInMonth->date->day) {
            $expenseDate->addMonth();
        }

        $incomes = App\Models\Income::orderBy('remaining', 'desc')->get();
        foreach ($incomes as $income) {
            if ($income->date->day <= $expense['day_due']) {
                App\Models\Expense::create([
                    'date' => $expenseDate,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'automatic_payment' => $expense->automatic_payment,
                    'account_taken_from' => $expense->account_taken_from,
                    'income_id' => $income->id,
                    'user_id' => Auth::id(),
                ]);
                $income->remaining -= $expense['amount'];
                $income->save(); 
                break;
            }
        }
    }   

}