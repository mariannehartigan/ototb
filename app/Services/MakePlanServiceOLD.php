<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MakePlanService
{
  public function makePlan(Carbon $date) {
    $this->insertIncomes($date);
    $this->insertExpenses($date);
  }

    private function insertIncomes(Carbon $date) {

      $plannedIncomes = \App\Models\PlannedIncome::where('user_id', Auth::id())->get();

      foreach ($plannedIncomes as $plannedIncome) {

        switch($plannedIncome->frequency) {

          case 'weekly':
            $depositDate = $date->copy()->startOfMonth();
            if ($depositDate->englishDayOfWeek !== $plannedIncome->day_of_week_for_weekly_biweekly) {
              $depositDate = $depositDate->next($plannedIncome->day_of_week_for_weekly_biweekly);
            }
            while ($depositDate->month === $date->month) {
              \App\Models\Income::create([
                'planned_income_id' => $plannedIncome->id,
                'date' => $depositDate,
                'description' => $plannedIncome->description,
                'amount' => $plannedIncome->amount,
                'remaining' => $plannedIncome->amount,
                'user_id' => Auth::id(),
              ]);
              $depositDate->addWeek();
            }
            break;

          case 'biweekly':
            $depositDate = Carbon::parse($plannedIncome->date);
            while ($depositDate->lt($date->copy()->startOfMonth())) {
                $depositDate->addWeeks(2);
            }
            while ($depositDate->month === $date->month && $depositDate->year === $date->year) {
                \App\Models\Income::create([
                    'planned_income_id' => $plannedIncome->id,
                    'date' => $depositDate,
                    'description' => $plannedIncome->description,
                    'amount' => $plannedIncome->amount,
                    'remaining' => $plannedIncome->amount,
                    'user_id' => Auth::id(),
                ]);

                $depositDate->addWeeks(2);
              }
            break;

          case 'monthly':

            $day = min($plannedIncome->day_deposited_for_monthly, $date->daysInMonth); // constrain to last day of month instead of 31st
            \App\Models\Income::create([
              'planned_income_id' => $plannedIncome->id,
              'date' => Carbon::create($date->year, $date->month, $day),
              'description' => $plannedIncome->description,
              'amount' => $plannedIncome->amount,
              'remaining' => $plannedIncome->amount,
              'user_id' => Auth::id(),
            ]);
            break;

          case 'quarterly':
            $depositDate = Carbon::parse($plannedIncome->date);
            while ($depositDate->lt($date->copy()->startOfMonth())) {
                $depositDate->addMonthsNoOverflow(3);
            }
            while ($depositDate->month === $date->month && $depositDate->year === $date->year) {
                \App\Models\Income::create([
                    'planned_income_id' => $plannedIncome->id,
                    'date' => $depositDate->copy(),
                    'description' => $plannedIncome->description,
                    'amount' => $plannedIncome->amount,
                    'remaining' => $plannedIncome->amount,
                    'user_id' => Auth::id(),
                ]);
                $depositDate->addMonthsNoOverflow(3);
            }
            break;
          
          case 'yearly':
            $depositDate = Carbon::parse($plannedIncome->date);
            while ($depositDate->lt($date->copy()->startOfMonth())) {
              $depositDate->addYearNoOverflow();
            }
            while ($depositDate->month === $date->month && $depositDate->year === $date->year) {
              \App\Models\Income::create([
                'planned_income_id' => $plannedIncome->id,
                'date' => $depositDate->copy(),
                'description' => $plannedIncome->description,
                'amount' => $plannedIncome->amount,
                'remaining' => $plannedIncome->amount,
                'user_id' => Auth::id(),
                ]);
              $depositDate->addYearNoOverflow();
            }
            break;
          
          case 'oneTime':
              $depositDate = Carbon::parse($plannedIncome->date);
              if ($depositDate->month === $date->month && $depositDate->year === $date->year) {
                  \App\Models\Income::create([
                      'planned_income_id' => $plannedIncome->id,
                      'date' => $depositDate,
                      'description' => $plannedIncome->description,
                      'amount' => $plannedIncome->amount,
                      'remaining' => $plannedIncome->amount,
                      'user_id' => Auth::id(),
                  ]);
              }
              break;
        }

      }
    
    }

    private function insertExpenses(Carbon $date) {
 
        // generateWeeklyExpensesArray()

        // generateBiweeklyExpensesArray()

        // generateMonthlyExpensesArray();

        // generateBalancedLowFrequencyExpensesArray()

        // generate5BudgetWeeklySavingsArray()

        // generateWeeklyBudgetsArray()

        // generateTithesArray()

        // allocateExpensesToIncomesAndInsert();  // includes splitting option and reducing tithes

            $monthlyExpenses = $this->generateMonthlyExpensesArray($date);
            $weeklyBudgets = $this->generateWeeklyBudgetsArray($date);

    $this->allocateExpensesToIncomesAndInsert(
        $date,
        $monthlyExpenses,
        $weeklyBudgets
    );


    }

      private function generateWeeklyExpensesArray(Carbon $date)
      {
        // ...
      }
      
      private function generateBiweeklyExpensesArray(Carbon $date)
      {
        // ...
      }
      
      private function generateMonthlyExpensesArray(Carbon $date)
      {
        $plannedExpenses = \App\Models\PlannedExpense::where('frequency', 'monthly')->where('user_id', Auth::id())->get();

        $monthlyExpenses = [];

        foreach ($plannedExpenses as $plannedExpense) {
            $day = (int) $plannedExpense->day_due;

            $lastDayOfMonth = $date->copy()->endOfMonth()->day;
            $day = min($day, $lastDayOfMonth);

            $expenseDate = $date->copy()->startOfMonth()->day($day);

            $monthlyExpenses[] = [
                'description' => $plannedExpense->description,
                'amount' => $plannedExpense->amount,
                'date' => $expenseDate->toDateString(),
                'account_taken_from' => $plannedExpense->account_taken_from,
                'tithe' => $plannedExpense->tithe,
                'automatic_payment' => $plannedExpense->automatic_payment,
                'automatically_generated_tithe' => false,
                'paid' => false,
                'notes' => $plannedExpense->notes,
                // 'income_id' => null,
                // 'position' => 0,
                'user_id' => Auth::id(),
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ];
        }

        return $monthlyExpenses;
      }

      private function generateBalancedLowFrequencyExpensesArray(Carbon $date)
      {
        // ...
      }
      
      private function generateTithesArray(Carbon $date)
      {
        // ...
      }
            
      private function generate5BudgetWeeklySavingsArray(Carbon $date)
      {
        // ...
      }

      private function generateWeeklyBudgetsArray(Carbon $date)
      {
        // find day_deposited_for_monthly first budget will be assigned to
        $totalPlannedIncomes = \App\Models\PlannedIncome::where('user_id', Auth::id())->sum('amount');
        $orderedPlannedIncomes = \App\Models\PlannedIncome::orderBy('day_deposited_for_monthly')->get();
        $cumulative = 0;
        $firstBudgetDayOfMonth = null;
        foreach ($orderedPlannedIncomes as $plannedIncome) {
            $cumulative += $plannedIncome->amount;
            if ($cumulative >= $totalPlannedIncomes/4.3423) {
                $firstBudgetDayOfMonth = $plannedIncome->day_deposited_for_monthly;
                break;
            }
        }
        
        // get date of first budget
        $budgetDayOfWeek = \App\Models\Setting::where('user_id', Auth::id())->value('budget_day_of_week');
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

    // finally, make array of weekly budgets
        $weeklyBudgets = [];
        for ($i = 0; $i < $numberOfBudgetDays; $i++) {
            $budgetDate = $firstBudgetDate->copy()->addDays($i * 7);
            $weeklyBudgets[] = [
                'description' => 'Budget ' . $budgetDate->format('F j'),
                'amount' => \App\Models\Budget::where('user_id', Auth::id())
                    ->where('frequency', 'weekly')
                    ->sum('amount'),
                'date' => $budgetDate,
                'day_due' => $budgetDate->day,
                'automatic_payment' => false,
                'account_taken_from' => 'debit',
                'notes' => null,
                'tithe' => false,
                'income_id' => 1,
                'user_id' => Auth::id(),
            ];
        }
        return $weeklyBudgets;
      }

  private function allocateExpensesToIncomesAndInsert(Carbon $date, array $monthlyExpenses, array $weeklyBudgets) {
    $expenses = array_merge($monthlyExpenses, $weeklyBudgets);
    foreach ($expenses as $expense) {
      \App\Models\Expense::create([
        'date' => $expense['date'],
        'description' => $expense['description'],
        'tithe' => $expense['tithe'],
        'amount' => $expense['amount'],
        'automatic_payment' => $expense['automatic_payment'],
        'account_taken_from' => $expense['account_taken_from'],
        'income_id' => 1,
        'user_id' => Auth::id(),
      ]);
    }
  }

}

