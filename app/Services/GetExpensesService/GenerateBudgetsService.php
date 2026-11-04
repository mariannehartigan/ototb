<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Income;
use App\Models\PlannedBudget;
use App\Models\Setting;
use App\Services\GetIncomesService;

class GenerateBudgetsService {
    
    public function __construct(      
        private GetIncomesService $getIncomesService,
    ) {}

    public function generateBudgetsArray(Carbon $date, array &$expenses): void {

        $plannedBudgets = PlannedBudget::with('plannedBudgetDetails')
            ->where('user_id', Auth::id())
            ->get();

        $settings = Setting::where('user_id', Auth::id())->first();

        $firstBudgetDayOfMonth = $this->calculateFirstBudgetDayOfMonth($date);



    }


    private function calculateFirstBudgetDayOfMonth(Carbon $date): int {

        // Get the hypothetical incomes for this month.
        $incomes = $this->getIncomesService->getIncomes(
            $date,
            useWorstCaseDates: true
        );

        // Only include weekly, biweekly and monthly incomes.
        $incomes = array_values(array_filter(
            $incomes,
            fn ($income) => in_array(
                $income['frequency'],
                ['weekly', 'biweekly', 'monthly'],
                true
            )
        ));

        $daysInMonth = $date->daysInMonth;

        $incomesByDay = array_fill(1, $daysInMonth, 0);


        // Organize incomes by their deposit day.
        foreach ($incomes as $income) {

            $day = Carbon::parse($income['date'])->day;

            $incomesByDay[$day] += (float) $income['amount'];

        }

        $totalIncome = array_sum($incomesByDay);

        // Calculate cumulative income for each day.
        $cumulativeIncome = [];
        $runningTotal = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $runningTotal += $incomesByDay[$day];

            $cumulativeIncome[$day] = $runningTotal;
        }

        // Find the earliest day satisfying all four thresholds.
        for ($firstDay = 1; $firstDay <= $daysInMonth; $firstDay++) {

            $valid = true;

            for ($week = 1; $week <= 4; $week++) {

                $budgetDay = $firstDay + ($week - 1) * 7;

                // Only count income from the current month.
                $dayToCheck = min($budgetDay, $daysInMonth);

                $incomeReceived = $cumulativeIncome[$dayToCheck];

                $requiredIncome = $totalIncome * ($week / 4);

                if ($incomeReceived < $requiredIncome - 0.001) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                return $firstDay;
            }
        }

        return $daysInMonth;
    }
        
}