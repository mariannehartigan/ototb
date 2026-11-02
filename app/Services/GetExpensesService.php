<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\GetExpensesService\GenerateBillsService;
use App\Services\GetExpensesService\GenerateBudgetsService;
use App\Models\Setting;

class GetExpensesService
{
    public function __construct(
        private GenerateBillsService $generateBillsService,
        private GenerateBudgetsService $generateBudgetsService,
    ) {}

    public function getExpenses(Carbon $date):array {

        $expenses = [];

        $settings = Setting::where('user_id', Auth::id())->first();

        $this->generateBillsService->generateBillsArray($date, $expenses);

        $this->generateBudgetsService->generateBudgetsArray($date, $expenses);

        return $expenses;

    }

}