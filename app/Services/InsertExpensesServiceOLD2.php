<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Setting;
use App\Models\Income;
use App\Models\PlannedExpense;
use App\Models\Expense;
use App\Services\ExpensesServices\InsertTithesService;
use App\Services\ExpensesServices\InitialExpenseAssignmentService;

class InsertExpensesService
{
    private array $expenses = [];

    public function __construct(
        private InsertTithesService $insertTithesService,
        private InitialExpenseAssignmentService $initialExpenseAssignmentService,
    ) {}

    public function insertExpenses(Carbon $date)
    {    
        $incomes = Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        $this->insertTithesService->insertTithes($date, $incomes);
        $this->initialExpenseAssignmentService->push($date);


        $settings = Setting::where('user_id', Auth::id())->first();
        $plannedExpenses = PlannedExpense::where('user_id', Auth::id())->get();
/*         $plannedVariableWeeklyExpenses = PlannedExpense::where('user_id', Auth::id())
            ->where('frequency', 'weekly')
            ->where('fixed_or_variable', 'variable')
            ->where('is_five_budget_month_savings', false)
            ->get(); */
        $incomes = Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        $this->expenses = []; // resets expenses[] array

        $this->pushPlannedExpenses($date, $plannedExpenses);
        // $this->pushSavingsForFiveBudgetMonths($date, $settings, $plannedVariableWeeklyExpenses);
        $this->pushWeeklyBudgets($date);
        // $this->pushTithes($date, $incomes);
        $this->allocateExpensesToIncomes($date);

        // Expense::insert($this->expenses);
    }


    private function pushPlannedExpenses(Carbon $date, Collection $plannedExpenses)
    {
        foreach($plannedExpenses as $plannedExpense) {
            switch($plannedExpense->frequency) {

                case 'weekly':
                    $this->pushWeeklyExpenses($date, $plannedExpense);
                    break;

                case 'biweekly':
                    $this->pushBiweeklyExpenses($date, $plannedExpense);
                    break;

                case 'monthly':
                    $this->pushMonthlyExpenses($date, $plannedExpense);
                    break;

                case 'quarterly':
                    $this->pushQuarterlyExpenses($date, $plannedExpense);
                    break;

                case 'yearly':
                    $this->pushYearlyExpenses($date, $plannedExpense);
                    break;

                case 'oneTime':
                    $this->pushOneTimeExpenses($date, $plannedExpense);
                    break;

            }
        }

    }

    private function pushWeeklyExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        //
    }
    
    private function pushBiweeklyExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        //
    }


    private function pushMonthlyExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        $day = min($plannedExpense->day_due_for_monthly, $date->daysInMonth);
        $this->expenses[] = [
            'description' => $plannedExpense->description,
            'amount' => $plannedExpense->amount,
            'date' => Carbon::create($date->year, $date->month, $day),
            'fixed_or_variable' => $plannedExpense->fixed_or_variable,
            'manual_or_automatic' => $plannedExpense->manual_or_automatic,
            'tithe' => $plannedExpense->tithe,
            'account_taken_from' => $plannedExpense->account_taken_from,
            'notes' => $plannedExpense->notes,
            'income_id' => $plannedExpense->planned_income_id,
            'user_id' => $plannedExpense->user_id,
        ];
    }

    
    private function pushQuarterlyExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        //
    }
    
    private function pushYearlyExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        //
    }
    
    private function pushOneTimeExpenses(Carbon $date, PlannedExpense $plannedExpense) {
        //
    }

/*     private function pushSavingsForFiveBudgetMonths(Carbon $date, Setting $settings, Collection $plannedVariableWeeklyExpenses)
    {
        $budgetConversionFactor = (364.75/(7*12)-4)/4;
        // to find the extra weekly amount that has to be saved for 5-Fridays
        // yields 0.0855654761904760
        // 4.3423 weeks per month, 364.75/(7*12)
        // subtract 4, so have to account for the extra 0.3423 weeks in a month
        // divide by 4, each week has to account for 0.3423/4 = 0.08557 (there are 4 budgets in a month)

        $savingsAmount = $plannedVariableWeeklyExpenses->sum('amount') * $budgetConversionFactor;

        if($settings->save_for_five_budget_months) {
            PlannedExpense::updateOrCreate(
                [
                    'description' => '5-' . $settings->budget_day_of_week,
                    'frequency' => 'weekly',
                    'fixed_or_variable' => 'variable',
                    'user_id' => Auth::id(),
                ],
                [
                    'amount' => $savingsAmount,
                    'is_five_budget_month_savings' => true,
                ]
            );
        }
    } */

    private function pushWeeklyBudgets(Carbon $date)
    {
        // ...
    }

    private function pushTithes(Carbon $date, Collection $incomes)
    {
        foreach($incomes as $income) {
            $this->expenses[] = [
                'description' => 'tithe',
                'amount' => $income->amount * 0.1,
                'date' => $income->date,
                'fixed_or_variable' => null,
                'manual_or_automatic' => null,
                'tithe' => true,
                'account_taken_from' => null,
                'notes' => null,
                'income_id' => $income->id,
                'user_id' => $income->user_id,
            ];
        }
    }

    private function allocateExpensesToIncomes(Carbon $date)
    {
        // ...
    }    
}