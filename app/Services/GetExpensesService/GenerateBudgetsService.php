<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
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

        $settings = Setting::with('settingChanges')
            ->where('user_id', Auth::id())
            ->first();

        $firstBudgetDayOfMonth = ($date);
        $numberOfBudgetsInMonth = $this->findNumberOfBudgetsInMonth($date);

        $previousBudgetsExist = Expense::where('user_id', Auth::id())
            ->where('is_weekly_budget', true)
            ->whereBetween('date', [
                $date->copy()->subMonthNoOverflow()->startOfMonth(),
                $date->copy()->subMonthNoOverflow()->endOfMonth()
            ])
            ->exists();

        // find date of first budget
        if (!$previousBudgetsExist) { // should end with no !... if ! then first runs

            $lastBudgetDatePreviousMonth = Expense::where('user_id', Auth::id())
                ->where('is_weekly_budget', true)
                ->where('date', '<', $date->copy()->startOfMonth())
                ->latest('date')
                ->first()?->date;

            $nextDate = Carbon::parse($lastBudgetDatePreviousMonth)->addDay();

            $firstBudgetDate = $this->findBudgetDates($nextDate, 1)[0] ?? null;
            
            dd($firstBudgetDate);

            // make array of dates of month that will need a budget using $firstBudgetDayOfMonth

        } else { // new account setup
            // findFirstBudgetDayOfMonth
            $firstBudgetDay = $this->findFirstBudgetDayNewAccount($date);

            $firstBudgetDate = $date->copy()
                ->startOfMonth()
                ->addDays($firstBudgetDay - 1);

            dd($firstBudgetDate);


            // make array of dates of month that will need a budget using $firstBudgetDayOfMonth
        }

            
            // make array of dates of month that will need a budget
            // -> also going to have to take into account future changes!!!

        // then take the array from either if statement and make an array of budgets to be put into expenses table
    }


    private function findBudgetDates(Carbon $startDate, int $numberOfBudgets = 4): array
        {
            $setting = Setting::with(['settingChanges' => function ($query) {
                $query->orderBy('id');
            }])
                ->where('user_id', Auth::id())
                ->first();

            if (!$setting) {
                return [];
            }

            $changes = $setting->settingChanges;
            $budgetDates = [];

            $currentDate = $startDate->copy();

            // Search up to 3 months ahead to find four budget dates.
            $endDate = $startDate->copy()->addMonths(3);

            while (
                $currentDate->lte($endDate) &&
                count($budgetDates) < $numberOfBudgets
            ) {
                $activeChange = null;

                foreach ($changes as $index => $change) {

                    $nextChange = $changes->get($index + 1);

                    // Stop using this setting after the next change's
                    // last_date_before_change.
                    if (
                        $nextChange?->last_date_before_change &&
                        $currentDate->gt(
                            Carbon::parse($nextChange->last_date_before_change)
                        )
                    ) {
                        continue;
                    }

                    // Don't use the new setting before its start date.
                    if (
                        $change->first_date_of_change &&
                        $currentDate->lt(
                            Carbon::parse($change->first_date_of_change)
                        )
                    ) {
                        continue;
                    }

                    $activeChange = $change;
                    break;
                }

                if (
                    $activeChange?->use_budget &&
                    $currentDate->englishDayOfWeek ===
                        $activeChange->budget_day_of_week
                ) {
                    $budgetDates[] = $currentDate->copy();
                }

                $currentDate->addDay();
            }
            return $budgetDates;
        }

    private function findFirstBudgetDayNewAccount(Carbon $date): int {
        
        $incomes = array_values(array_filter(
            $this->getIncomesService->getIncomes($date, useWorstCaseDates: true),
            fn ($income) => in_array(
                $income['frequency'],
                ['weekly', 'biweekly', 'monthly'],
                true
            )
        ));

        $daysInMonth = $date->daysInMonth;
        $incomesByDay = array_fill(1, $daysInMonth, 0);

        // Organize by deposit day
        foreach ($incomes as $income) {
            $day = Carbon::parse($income['date'])->day;
            $incomesByDay[$day] += (float) $income['amount'];
        }

        $totalIncome = array_sum($incomesByDay);
        $cumulativeIncome = [];
        $runningTotal = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $runningTotal += $incomesByDay[$day];
            $cumulativeIncome[$day] = $runningTotal;
        }

        // Find the earliest day satisfying all four thresholds
        for ($firstDay = 1; $firstDay <= $daysInMonth; $firstDay++) {

            $valid = true;
            for ($week = 1; $week <= 4; $week++) {
                $budgetDay = $firstDay + ($week - 1) * 7;
                $dayToCheck = min($budgetDay, $daysInMonth);
                $incomeReceived = $cumulativeIncome[$dayToCheck];
                $requiredIncome = $totalIncome * ($week / 4);
            
                // do we need all this valid/if statement?
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


    private function findNumberOfBudgetsInMonth(Carbon $date): int
    {
        $setting = Setting::with(['settingChanges' => function ($query) {
            $query->orderBy('id');
        }])
            ->where('user_id', Auth::id())
            ->first();

        $changes = $setting->settingChanges;

        $arrayOfBudgetDates = [];

        $currentDateIteration = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        while ($currentDateIteration->lte($monthEnd)) {

            $budgetDayOfWeek = null;
            $useBudget = false;

            foreach ($changes as $index => $change) {

                $nextChange = $changes->get($index + 1);

                // The next change determines when this setting ends.
                if (
                    $nextChange &&
                    $nextChange->last_date_before_change &&
                    $currentDateIteration->gt(
                        Carbon::parse($nextChange->last_date_before_change)
                    )
                ) {
                    continue;
                }

                // This setting begins on its first_date_of_change.
                if (
                    $change->first_date_of_change &&
                    $currentDateIteration->lt(
                        Carbon::parse($change->first_date_of_change)
                    )
                ) {
                    continue;
                }

                $budgetDayOfWeek = $change->budget_day_of_week;
                $useBudget = $change->use_budget;

                break;
            }

            if (
                $useBudget &&
                $currentDateIteration->englishDayOfWeek === $budgetDayOfWeek
            ) {
                $arrayOfBudgetDates[] = $currentDateIteration->day;
            }

            $currentDateIteration->addDay();
        }

        return count($arrayOfBudgetDates);
    }



/*     private function findNumberOfBudgetsInMonth(Carbon $date): int {
           
            $budgetDayOfWeek = Setting::where('user_id', Auth::id())
                ->first()
                ?->settingChanges()
                ->orderByDesc('first_date_of_change')
                ->value('budget_day_of_week');

            $arrayOfBudgetDates = [];
            $currentDateIteration = $date->copy()->startOfMonth();
            while ($date->month === $currentDateIteration->month) {
                if ($currentDateIteration->format('l') === $budgetDayOfWeek) {
                    $arrayOfBudgetDates[] = $currentDateIteration->day;
                }
                $currentDateIteration->addDay();
            }
            $numberOfBudgetsInMonth = count($arrayOfBudgetDates);

            return $numberOfBudgetsInMonth;
    } */
    
}