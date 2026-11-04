<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MakePlanService
{
    public function __construct(      
        private InsertIncomesService $insertIncomesService,
        private GetExpensesService $getExpensesService,
        private InsertAutomaticTithesService $insertAutomaticTithesService,
        private InitialExpenseAssignmentService $initialExpenseAssignmentService,
        private BalanceExpensesService $balanceExpensesService,
    ) {}

    public function makePlan(Carbon $date): void {
        $this->insertIncomesService->insertIncomes($date);
        $this->getExpensesService->getExpenses($date);
        $this->insertAutomaticTithesService->insertAutomaticTithes($date);
        $this->initialExpenseAssignmentService->initialExpenseAssignment($date);
        $this->balanceExpensesService->balanceExpenses($date);
    }
}