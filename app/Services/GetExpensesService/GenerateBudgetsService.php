<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedBudget;

class GenerateBudgetsService
{
    public function generateBudgetsArray(Carbon $date, array &$expenses): void
    {
        $plannedBudgets = PlannedBudget::with('plannedBudgetDetails')
            ->where('user_id', Auth::id())
            ->get();
    }
}