<?php

namespace App\Services;

use Carbon\Carbon;
use App\Services\InsertIncomesService;
use App\Services\InsertExpensesService;


class MakePlanService
{
    public function __construct(
        private InsertIncomesService $insertIncomes,
        private InsertExpensesService $insertExpenses,
    ) {}

    public function makePlan(Carbon $date)
    {
        $this->insertIncomes->insertIncomes($date);
        $this->insertExpenses->insertExpenses($date);
    }
}