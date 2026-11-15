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

        $previousBudgetsExist = Expense::where('user_id', Auth::id())
            ->where('is_weekly_budget', true)
            ->whereBetween('date', [
                $date->copy()->subMonthNoOverflow()->startOfMonth(),
                $date->copy()->subMonthNoOverflow()->endOfMonth()
            ])
            ->exists();


        // find $firstBudgetDAte
        if (!$previousBudgetsExist) { // should end with no !... if ! then first runs
            $firstBudgetDate = $this->findFirstBudgetDateExistingAccount($date);
/*             $lastBudgetDatePreviousMonth = Expense::where('user_id', Auth::id())
                ->where('is_weekly_budget', true)
                ->whereDate('month_year', $date->copy()->subMonthNoOverflow()->startOfMonth())
                ->latest('date')
                ->first()?->date;
            $nextDate = Carbon::parse($lastBudgetDatePreviousMonth)->addDay();
            $firstBudgetDate = $this->findBudgetDates($nextDate, 1)[0] ?? null; */
        } else {
            $firstBudgetDate = $this->findFirstBudgetDateNewAccount($date);
            }

        $numberOfBudgetsInMonth = $this->findNumberOfBudgetsInMonth($date);
        dd($numberOfBudgetsInMonth);

        $nextBudgetDates = $this->findNextBudgetDates($firstBudgetDate, $numberOfBudgetsInMonth);
        dd($nextBudgetDates);
        
    }

    private function findFirstBudgetDateExistingAccount(Carbon $date) {
        $lastBudgetDatePreviousMonth = Expense::where('user_id', Auth::id())
            ->where('is_weekly_budget', true)
            ->whereDate('month_year', $date->copy()->subMonthNoOverflow()->startOfMonth())
            ->latest('date')
            ->first()?->date;

        $nextDate = Carbon::parse($lastBudgetDatePreviousMonth)->addDay();
        $budgetDates = $this->findBudgetDates($nextDate, 1);
        $firstBudgetDateCurrentMonth = $budgetDates[0] ?? null;

        return $firstBudgetDateCurrentMonth;
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

private function findFirstBudgetDateNewAccount(Carbon $date): Carbon
{
    $incomes = array_values(array_filter(
        $this->getIncomesService->getIncomes(
            $date,
            useWorstCaseDates: true
        ),
        fn ($income) => in_array(
            $income['frequency'],
            ['weekly', 'biweekly', 'monthly'],
            true
        )
    ));

    $daysInMonth = $date->daysInMonth;
    $incomesByDay = array_fill(1, $daysInMonth, 0);

    // Organize incomes by deposit day.
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

    $monthStart = $date->copy()->startOfMonth();
    $monthEnd = $date->copy()->endOfMonth();

    // Get actual budget dates, including dates in the next month.
    $budgetDates = $this->findBudgetDates($monthStart, 10);

    // Try each possible starting budget date.
    foreach ($budgetDates as $startIndex => $firstBudgetDate) {

        // We need four budget dates to test the thresholds.
        $fourBudgetDates = array_slice($budgetDates, $startIndex, 4);

        if (count($fourBudgetDates) < 4) {
            break;
        }

        $valid = true;

        foreach ($fourBudgetDates as $index => $budgetDate) {

            // Use only income from the requested month.
            if ($budgetDate->gt($monthEnd)) {
                $dayToCheck = $daysInMonth;
            } else {
                $dayToCheck = $budgetDate->day;
            }

            $incomeReceived = $cumulativeIncome[$dayToCheck];

            // 25%, 50%, 75%, 100%.
            $requiredIncome = $totalIncome * (($index + 1) / 4);

            if ($incomeReceived < $requiredIncome - 0.001) {
                $valid = false;
                break;
            }
        }

        if ($valid) {
            return $firstBudgetDate->copy();
        }
    }

    throw new \RuntimeException('No valid first budget date found.');
}



private function findNumberOfBudgetsInMonth(Carbon $date): int
{
    $setting = Setting::with(['settingChanges' => function ($query) {
        $query->orderBy('id');
    }])
        ->where('user_id', Auth::id())
        ->first();

    if (!$setting) {
        return 0;
    }

    $changes = $setting->settingChanges;
    $numberOfBudgets = 0;

    $currentDate = $date->copy()->startOfMonth();
    $monthEnd = $date->copy()->endOfMonth();

    while ($currentDate->lte($monthEnd)) {

        $activeChange = null;

        foreach ($changes as $index => $change) {

            $nextChange = $changes->get($index + 1);

            // Check whether this setting has ended.
            if (
                $nextChange?->last_date_before_change &&
                $currentDate->gt(
                    Carbon::parse($nextChange->last_date_before_change)
                )
            ) {
                continue;
            }

            // Check whether this setting has started.
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

        // Count the date if it matches the active budget weekday.
        if (
            $activeChange?->use_budget &&
            $currentDate->englishDayOfWeek ===
                $activeChange->budget_day_of_week
        ) {
            $numberOfBudgets++;
        }

        $currentDate->addDay();
    }

    return $numberOfBudgets;
}





private function findNextBudgetDates(
    Carbon $firstBudgetDate,
    int $numberOfBudgetsInMonth
): array {
    if ($numberOfBudgetsInMonth <= 0) {
        return [];
    }

    $setting = Setting::with(['settingChanges' => function ($query) {
        $query->orderBy('id');
    }])
        ->where('user_id', Auth::id())
        ->first();

    if (!$setting) {
        return [];
    }

    $changes = $setting->settingChanges;

    // Include the first budget date in the array.
    $budgetDates = [$firstBudgetDate->copy()];

    // Start searching the day after the first budget.
    $currentDate = $firstBudgetDate->copy()->addDay();

    $endDate = $currentDate->copy()->addMonths(3);

    while (
        $currentDate->lte($endDate) &&
        count($budgetDates) < $numberOfBudgetsInMonth
    ) {
        $activeChange = null;

        foreach ($changes as $index => $change) {

            $nextChange = $changes->get($index + 1);

            // Check when the previous setting ends.
            if (
                $nextChange?->last_date_before_change &&
                $currentDate->gt(
                    Carbon::parse($nextChange->last_date_before_change)
                )
            ) {
                continue;
            }

            // Check when the new setting begins.
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


   
}