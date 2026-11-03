<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Income;
use App\Models\PlannedBudget;
use App\Models\Setting;

class GenerateBudgetsService {

    public function generateBudgetsArray(Carbon $date, array &$expenses): void {
        $plannedBudgets = PlannedBudget::with('plannedBudgetDetails')
            ->where('user_id', Auth::id())
            ->get();

        $settings = Setting::where('user_id', Auth::id())->first();

        $firstBudgetDayOfMonth = $this->calculateFirstBudgetDayOfMonth();
    }




private function calculateFirstBudgetDayOfMonth(): int
{
    $plannedIncomes = \App\Models\PlannedIncome::with('plannedIncomeDetails')
        ->where('user_id', Auth::id())
        ->get();

    $incomesByDay = array_fill(1, 31, 0);

    // Build the worst-case income schedule.
    foreach ($plannedIncomes as $plannedIncome) {

        $detail = $plannedIncome->plannedIncomeDetails
            ->sortBy('effective_date')
            ->last();

        if (!$detail) {
            continue;
        }

        $amount = (float) $detail->amount;

        if ($detail->frequency === 'weekly') {

            foreach ([7, 14, 21, 28] as $day) {
                $incomesByDay[$day] += $amount;
            }

        } elseif ($detail->frequency === 'biweekly') {

            foreach ([14, 28] as $day) {
                $incomesByDay[$day] += $amount;
            }

        } elseif ($detail->frequency === 'monthly') {

            $day = (int) $detail->day_deposited_for_monthly;

            if ($day >= 1 && $day <= 31) {
                $incomesByDay[$day] += $amount;
            }
        }
    }

    $totalIncome = array_sum($incomesByDay);

    // Calculate cumulative income for each day.
    $cumulativeIncome = [];
    $runningTotal = 0;

    for ($day = 1; $day <= 31; $day++) {
        $runningTotal += $incomesByDay[$day];
        $cumulativeIncome[$day] = $runningTotal;
    }

    // Find the earliest day satisfying all four thresholds.
    for ($firstDay = 1; $firstDay <= 31; $firstDay++) {

        $valid = true;

        for ($week = 1; $week <= 4; $week++) {

            $budgetDay = $firstDay + ($week - 1) * 7;

            // Only count income from the current month.
            $dayToCheck = min($budgetDay, 31);

            $incomeReceived = $cumulativeIncome[$dayToCheck];

            $requiredIncome = $totalIncome * ($week / 4);

            if ($incomeReceived < $requiredIncome - 0.001) {
                $valid = false;
                break;
            }
        }
        dd($firstDay);

        if ($valid) {
            return $firstDay;
        }
    }

    return 31;
}


        
}