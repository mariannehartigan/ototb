<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MakePlanService
{
    public function __construct(      
        private InsertIncomesService $insertIncomesService,
        private GetExpensesService $getExpensesService,
        private AddAutomaticTithesService $addAutomaticTithesService,
        private InitialExpenseAssignmentService $initialExpenseAssignmentService,
        private BalanceExpensesService $balanceExpensesService,
    ) {}

    public function makePlan(Carbon $date): void {
        $this->insertIncomesService->insertIncomes($date);
        $expenses = $this->getExpensesService->getExpenses($date);
        $this->addAutomaticTithesService->insertAutomaticTithes($date);
        $expenses = $this->initialExpenseAssignmentService->initialExpenseAssignment($date, $expenses);
        $expenses = $this->balanceExpensesService->balanceExpenses($date);
    }
}