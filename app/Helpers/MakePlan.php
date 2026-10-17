<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

function makePlan(Carbon $date)
{
    // POPULATE INCOME TABLE FROM PLANNED INCOMES
    // $plannedIncomes = App\Models\PlannedIncome::where('user_id', Auth::id())->get();

    // foreach ($plannedIncomes as $plannedIncome) {
    //     $day = min($plannedIncome->day_deposited, $date->daysInMonth); // constrain to last day of month instead of 31st
    //     App\Models\Income::create([
    //         'planned_income_id' => $plannedIncome->id,
    //         'date' => Carbon::create($date->year, $date->month, $day),
    //         'description' => $plannedIncome->description,
    //         'amount' => $plannedIncome->amount,
    //         'remaining' => $plannedIncome->amount,
    //         'user_id' => Auth::id(),
    //     ]);
    // }

    //     $incomes = App\Models\Income::where('user_id', Auth::id())
    //         ->whereMonth('date', $date->month)
    //         ->whereYear('date', $date->year)
    //         ->orderBy('date')
    //         ->get();

    // POPULATE EXPENSE TABLE WITH TITHES

    $settings = App\Models\Setting::where('user_id', Auth::id())->first();

    if ($settings && $settings->tithe) {
        foreach ($incomes as $income) {
            App\Models\Expense::create([
                'date' => $income->date,
                'description' => 'Tithe',
                'tithe' => true,
                'automatically_generated_tithe' => true,
                'amount' => $income->amount * 0.1,
                'automatic_payment' => false,
                // 'account_taken_from' => 'debit',
                'income_id' => $income->id,
                'user_id' => Auth::id(),
            ]);
        }
    }

// POPULATE EXPENSE TABLE FROM MONTHLY, WEEKLY BUDGETS AND PLANNED EXPENSES

    $firstIncomeInMonth = App\Models\Income::where('user_id', Auth::id())
    ->whereMonth('date', $date->month)
    ->whereYear('date', $date->year)
    ->orderBy('date')
    ->first();

    // MAKE ARRAY OF MONTHLY BUDGETS

    $monthlyBudgets = App\Models\Budget::where('user_id', Auth::id())
        ->where('frequency', 'monthly')
        ->get()
        ->map(function ($budget) use ($date, $firstIncomeInMonth) {

            $budget->day_due = $budget->date_available;

            $budget->date = Carbon::create(
                $date->year,
                $date->month,
                min($budget->day_due, $date->daysInMonth)
            );

            if ($budget->day_due < $firstIncomeInMonth->date->day) {
                $budget->date->addMonth();
            }

            $budget->automatic_payment = false;
            $budget->account_taken_from = 'debit';
            $budget->notes = null;
            $budget->tithe = false;

            return $budget;
        });

    // START WEEKLY STUFF
    $weeklyBudgets = [];
    if ($settings && $settings->budget_type === '5 budgets per month') {

        // INSERT WEEKLY SAVINGS INTO BUDGET
        $budgetConversionFactor = ((365.2425/(7*12))-4)/4;  // yields 0.08703125
        // 4.348125 weeks per month, 365.2425/(7*12)
        // subtract 4, so have to account for the extra 0.348125 weeks in a month
        // divide by 4, each week has to account for 0.348125/4 = 0.08703125 (there are 4 budgets in a month)

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

        // MAKE ARRAY OF WEEKLY BUDGETS

        // find day_deposited first budget will be assigned to
        $totalPlannedIncomes = App\Models\PlannedIncome::where('user_id', Auth::id())->sum('amount');
        $orderedPlannedIncomes = App\Models\PlannedIncome::orderBy('day_deposited')->get();
        $cumulative = 0;

        $firstBudgetDayOfMonth = null;
        foreach ($orderedPlannedIncomes as $plannedIncome) {
            $cumulative += $plannedIncome->amount;
            if ($cumulative >= $totalPlannedIncomes/4.348125) {
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

    // FINALLY, MAKE ARRAY OF WEEKLY BUDGETS:
    $weeklyBudgets = collect();

    for ($i = 0; $i < $numberOfBudgetDays; $i++) {
        $budgetDate = $firstBudgetDate->copy()->addDays($i * 7);

        $weeklyBudgets->push((object) [
            'description' => 'Budget ' . $budgetDate->format('F j'),
            'amount' => App\Models\Budget::where('user_id', Auth::id())
                ->where('frequency', 'weekly')
                ->sum('amount'),
            'date' => $budgetDate,
            'day_due' => $budgetDate->day,
            'automatic_payment' => false,
            'account_taken_from' => 'debit',
            'notes' => null,
            'tithe' => false,
            'user_id' => Auth::id(),
        ]);
    }

    } // end if weekly budget === '5 budgets per month'

    // POPULATE EXPENSE TABLE

    $plannedExpenses = App\Models\PlannedExpense::where('user_id', Auth::id())
        ->get()
        ->map(function ($expense) use ($date, $firstIncomeInMonth) {
            $expense->date = Carbon::create(
                $date->year,
                $date->month,
                min($expense->day_due, $date->daysInMonth)
            );
            if ($expense->day_due < $firstIncomeInMonth->date->day) {
                $expense->date->addMonth();
            }
            return $expense;
        });

    $combinedExpenses = $plannedExpenses
        ->concat($monthlyBudgets)
        ->concat($weeklyBudgets)
        ->sortBy('date')
        ->values();

    foreach ($combinedExpenses as $expense) {

        if (isset($expense->planned_income_id) && $expense->planned_income_id !== null) {
            App\Models\Expense::create([
                'date' => $expense->date,
                'description' => $expense->description,
                'tithe' => $expense->tithe,
                'amount' => $expense->amount,
                'automatic_payment' => $expense->automatic_payment,
                'account_taken_from' => $expense->account_taken_from,
                'income_id' => $income->id,
                // 'income_id' => $expense->planned_income_id,
                'user_id' => Auth::id(),
            ]);

            $income->remaining -= $expense->amount;
            $income->save();
        } else {
          $income = $incomes
              ->filter(fn ($income) => $income->date <= $expense->date)
              ->sortBy(function ($income) use ($expense, $incomes) {

                  // simulate applying this expense
                  $newRemaining = $income->remaining - $expense->amount;

                  // simulate the resulting balances
                  $balances = $incomes
                      ->filter(fn ($i) => $i->date <= $expense->date)
                      ->map(function ($i) use ($income, $newRemaining) {
                          return $i->id === $income->id
                              ? $newRemaining
                              : $i->remaining;
                      });

                  // smaller spread = better
                  return collect([
                      $balances->max() - $balances->min(),
                      abs($newRemaining),
                      -$income->date->timestamp,
                  ]);
              })
              ->first();

          App\Models\Expense::create([
              'date' => $expense->date,
              'description' => $expense->description,
              'tithe' => $expense->tithe,
              'amount' => $expense->amount,
              'automatic_payment' => $expense->automatic_payment,
              'account_taken_from' => $expense->account_taken_from,
              'income_id' => $income->id,
              'user_id' => Auth::id(),
          ]);

          $income->remaining -= $expense->amount;
          $income->save();
        }
    }

    // CHECK IF DUPLICATE VALUES ARE ASSIGNED UNCHRONOLOGICALLY
    // THIS CODE WAS COMMENTED OUT BECAUSE CAUSING PROBLEMS WITH REDUCING AUTOMATIC TITHES WITH EXPENSE TITHES
// $expenseAmounts = App\Models\Expense::with('income')
//     ->where('user_id', Auth::id())
//     ->orderBy('amount')
//     ->orderBy('date')
//     ->get()
//     ->groupBy('amount');

//   foreach ($expenseAmounts as $amountGroup) {

//       $ordered = $amountGroup->sortBy('date')->values();

//       for ($i = 0; $i < $ordered->count() - 1; $i++) {
//           for ($j = $i + 1; $j < $ordered->count(); $j++) {

//               $earlier = $ordered[$i];
//               $later = $ordered[$j];

//               // If they're already in chronological income order, nothing to do.
//               if ($earlier->income->date <= $later->income->date) {
//                   continue;
//               }

//               // Would swapping still respect income-before-expense?
//               if (
//                   $later->income->date <= $earlier->date &&
//                   $earlier->income->date <= $later->date
//               ) {
//                   $earlierIncomeId = $earlier->income_id;

//                   $earlier->income_id = $later->income_id;
//                   $later->income_id = $earlierIncomeId;

//                   $earlier->save();
//                   $later->save();

//                   // Refresh relationships for any further comparisons
//                   $earlier->load('income');
//                   $later->load('income');
//               }
//           }
//       }
//   }






    // REDUCE AUTOMATIC TITHE AMOUNT BY OTHER EXPENSES THAT ARE TITHE

    if ($settings && $settings->tithe) {

        // $incomesSortedLastDayFirst = App\Models\Income::where('user_id', Auth::id())->latest()->get();
        $incomesSortedLastDayFirst = App\Models\Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderByDesc('date')
            ->get();

        $carryover = 0;

        foreach ($incomesSortedLastDayFirst as $income) {

            $expenses = App\Models\Expense::where([
                'user_id' => Auth::id(),
                'income_id' => $income->id,
            ])->get();

            $manualTithes = $expenses
                ->where('tithe', true)
                ->where('automatically_generated_tithe', false)
                ->sum('amount');

            $automaticTithe = $expenses
                ->firstWhere('automatically_generated_tithe', true);

            $amountToRemove = $manualTithes + $carryover;

            $amountToRemove = $manualTithes + $carryover;

            $manualTitheExpenses = $expenses
                ->where('tithe', true)
                ->where('automatically_generated_tithe', false);

            $manualTithes = $manualTitheExpenses->sum('amount');



            if ($automaticTithe->amount >= $amountToRemove) {

                // This income can absorb it all.
                $automaticTithe->amount -= $amountToRemove;
                $automaticTithe->save();

                $carryover = 0;

            } else {

                // Use up this automatic tithe and carry the rest.
                $carryover = $amountToRemove - $automaticTithe->amount;

                $automaticTithe->amount = 0;
                $automaticTithe->save();
            }
        }


    }
}