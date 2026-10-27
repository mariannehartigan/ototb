<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Income;
use App\Models\PlannedExpense;
use App\Models\Expense;
use App\Services\AssignAutomaticTithesService;
use App\Services\AssignAllOtherExpensesService;
/* use App\Services\ExpensesServices\InsertTenPercentTithesService;
use App\Services\ExpensesServices\InsertExpenseNoIncomeIdService;
use App\Services\ExpensesServices\InsertExpensesService;
use App\Services\ExpensesServices\AllocateExpensesToIncomeIdService;
use App\Services\ExpensesServices\AdjustTithesService; */


class MakePlanService
{
    public function __construct(
        private \App\Services\InsertIncomesService $insertIncomes,
        // private \App\Services\AssignTitheExpensesService $assignTitheExpenses,
        private \App\Services\ExpensesServices\InsertAutomaticTithesService $insertAutomaticTithes,
        private \App\Services\ExpensesServices\InsertExpensesService $insertExpenses,
        // private \App\Services\AssignAllOtherExpensesService $assignAllOtherExpenses,
/*         private InsertTenPercentTithesService $insertTenPercentTithes,
        private InsertExpensesService $insertExpenses,
        private AllocateExpensesToIncomeIdService $allocateExpensesToIncomeId,
        private AdjustTithesService $adjustTithes, */
    ) {}

    public function makePlan(Carbon $date)
    {
        $this->insertIncomes->insertIncomes($date);

        $incomes = Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date', 'desc')
            ->get();

        $endOfMonth = $date->copy()->endOfMonth();
/*         $tithePlannedExpenses = PlannedExpense::where('user_id', Auth::id())
            ->where('tithe', true)
            ->with([
                'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                    $query
                        ->whereDate('effective_date', '<=', $endOfMonth)
                        ->orderBy('effective_date');
                }
            ])
            ->get();
        
        $allOtherPlannedExpenses = PlannedExpense::where('user_id', Auth::id())
            ->where('tithe', false)
            ->with([
                'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                    $query
                        ->whereDate('effective_date', '<=', $endOfMonth)
                        ->orderBy('effective_date');
                }
            ])
            ->get(); */

        $plannedExpenses = PlannedExpense::where('user_id', Auth::id())
            ->with([
                'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                    $query
                        ->whereDate('effective_date', '<=', $endOfMonth)
                        ->orderBy('effective_date');
                }
            ])
            ->get();

        // $this->insertExpenses->insertExpenses($date, $incomes, $tithePlannedExpenses);
        $this->insertExpenses->insertExpenses($date, $incomes, $plannedExpenses);
        $this->insertAutomaticTithes->insertAutomaticTithes($date, $incomes);
        // $this->insertExpenses->insertExpenses($date, $incomes, $allOtherPlannedExpenses);




/*         $this->insertTenPercentTithes->insertTenPercentTithes($date, $incomes);
        $this->insertExpenses->insertExpenses($date, $incomes);

        $expenses = Expense::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        foreach($expenses as $expense) {    
            $this->allocateExpensesToIncomeId->allocateExpensesToIncomeId($date, $incomes, $expense);
        }
        
        $this->adjustTithes->adjustTithes($date, $incomes); */
    }
}