<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Income;
use App\Models\Expense;
use App\Services\InsertIncomesService;
use App\Services\ExpensesServices\InsertTenPercentTithesService;
use App\Services\ExpensesServices\InsertExpenseNoIncomeIdService;
use App\Services\ExpensesServices\InsertTitheExpensesService;
use App\Services\ExpensesServices\AllocateExpensesToIncomeIdService;
use App\Services\InsertExpensesService;


class MakePlanService
{
    public function __construct(
        private InsertIncomesService $insertIncomes,
        private InsertTenPercentTithesService $insertTenPercentTithes,
        private InsertTitheExpensesService $insertTitheExpenses,
        private AllocateExpensesToIncomeIdService $allocateExpensesToIncomeId,
        // private InsertExpensesService $insertExpenses,
    ) {}

    public function makePlan(Carbon $date)
    {
        $this->insertIncomes->insertIncomes($date);

        $incomes = Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        $this->insertTenPercentTithes->insertTenPercentTithes($date, $incomes);
        $this->insertTitheExpenses->insertTitheExpenses($date, $incomes);

        $expenses = Expense::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        foreach($expenses as $expense) {    
            $this->allocateExpensesToIncomeId->allocateExpensesToIncomeId($date, $incomes, $expense);
        }
        // $this->insertExpenses->insertExpenses($date);
    }
}