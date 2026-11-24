<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\Setting;
use App\Services\GetIncomesService;

class GenerateBudgetsService {
    
    public function __construct(      
        private GetIncomesService $getIncomesService,
    ) {}

    public function generateBudgetsArray(Carbon $date, array &$expenses): void {

        $previousBudgetsExist = Expense::where('user_id', Auth::id())
            ->where('is_weekly_budget', true)
            ->whereBetween('date', [
                $date->copy()->subMonthNoOverflow()->startOfMonth(),
                $date->copy()->subMonthNoOverflow()->endOfMonth()
            ])
            ->exists();

        if (!$previousBudgetsExist) { // should end with no !... if ! then first runs
            $firstBudgetDate = $this->findFirstBudgetDateExistingAccount($date);
        } else {
            $firstBudgetDate = $this->findFirstBudgetDateNewAccount($date);
        }

        $numberOfBudgetsInMonth = $this->findNumberOfBudgetsInMonth($date);

        $budgetDates = $this->findBudgetDates($firstBudgetDate, $numberOfBudgetsInMonth);

        $this->pushBudgets($date, $budgetDates, $expenses);

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


    private function findBudgetDates(Carbon $startDate, int $numberOfBudgets): array
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

                    if (
                        $nextChange?->last_date_before_change &&
                        $currentDate->gt(
                            Carbon::parse($nextChange->last_date_before_change)
                        )
                    ) {
                        continue;
                    }

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


/* private function pushBudgets(Carbon $date, array $budgetDates, array &$expenses): void {

    $weeklyBudgetAmount = \App\Models\PlannedBudgetDetail::whereHas(
        'plannedBudget',
        function ($query) {
            $query->where('user_id', Auth::id());
        }
    )
        ->where('frequency', 'weekly')
        ->sum('amount');

    foreach ($budgetDates as $budgetDate) {

        $expenses[] = [
            'description' => 'Budget ' . $budgetDate->format('F j'),
            'amount' => $weeklyBudgetAmount,
            'date' => $budgetDate->toDateString(),
            'month_year' => $date->copy()->startOfMonth()->toDateString(),
            'day_due' => $budgetDate->day,
            'automatic_payment' => false,
            'account_taken_from' => 'debit',
            'notes' => null,
            'tithe' => false,
            'income_id' => null,
            'is_weekly_budget' => true,
            'user_id' => Auth::id(),
        ];
    }
} */


private function pushBudgets(
    Carbon $date,
    array $budgetDates,
    array &$expenses
): void {

    $monthStart = $date->copy()->startOfMonth();
    $monthEnd = $date->copy()->endOfMonth();

    $plannedBudgets = \App\Models\PlannedBudget::with('plannedBudgetDetails')
        ->where('user_id', Auth::id())
        ->get();

    // WEEKLY BUDGETS
    // Combine all weekly budgets into one amount for each budget date.

    foreach ($budgetDates as $budgetDate) {

        $weeklyBudgetAmount = 0;

        foreach ($plannedBudgets as $plannedBudget) {

            $detail = $plannedBudget->plannedBudgetDetails
                ->filter(function ($detail) use ($budgetDate) {
                    return (
                        !$detail->first_date_of_change ||
                        Carbon::parse($detail->first_date_of_change)->lte($budgetDate)
                    );
                })
                ->sortByDesc('first_date_of_change')
                ->first();

            if ($detail?->frequency === 'weekly') {
                $weeklyBudgetAmount += (float) $detail->amount;
            }
        }

        $expenses[] = [
            'description' => 'Budget ' . $budgetDate->format('F j'),
            'amount' => $weeklyBudgetAmount,
            'date' => $budgetDate->toDateString(),
            'month_year' => $monthStart->toDateString(),
            'day_due' => $budgetDate->day,
            'automatic_payment' => false,
            'account_taken_from' => 'debit',
            'notes' => null,
            'tithe' => false,
            'income_id' => null,
            'is_weekly_budget' => true,
            'user_id' => Auth::id(),
        ];
    }

    // MONTHLY, QUARTERLY, YEARLY AND ONE-TIME BUDGETS
    // Add each budget individually.

    foreach ($plannedBudgets as $plannedBudget) {

        $currentDate = $monthStart->copy();

        while ($currentDate->lte($monthEnd)) {

            $detail = $plannedBudget->plannedBudgetDetails
                ->filter(function ($detail) use ($currentDate) {
                    return (
                        !$detail->first_date_of_change ||
                        Carbon::parse($detail->first_date_of_change)->lte($currentDate)
                    );
                })
                ->sortByDesc('first_date_of_change')
                ->first();

            if (!$detail) {
                $currentDate->addDay();
                continue;
            }

            $isDue = false;

            switch ($detail->frequency) {

                case 'monthly':
                    $day = min(
                        $detail->day_for_monthly,
                        $currentDate->daysInMonth
                    );

                    $isDue = $currentDate->day === $day;
                    break;

                case 'quarterly':
                case 'yearly':

                    $anchorDate = $detail->date_for_biweekly_quarterly_yearly_onetime;

                    if ($anchorDate) {

                        $anchor = Carbon::parse($anchorDate);

                        $intervalMonths = $detail->frequency === 'quarterly'
                            ? 3
                            : 12;

                        $monthsDifference =
                            ($currentDate->year - $anchor->year) * 12 +
                            ($currentDate->month - $anchor->month);

                        if (
                            $currentDate->gte($anchor) &&
                            $monthsDifference >= 0 &&
                            $monthsDifference % $intervalMonths === 0
                        ) {
                            $expectedDate = $anchor->copy()
                                ->addMonthsNoOverflow($monthsDifference);

                            $isDue = $currentDate->isSameDay($expectedDate);
                        }
                    }
                    break;

                case 'onetime':
                    $anchorDate = $detail->date_for_biweekly_quarterly_yearly_onetime;

                    $isDue = $anchorDate &&
                        $currentDate->isSameDay(Carbon::parse($anchorDate));
                    break;
            }

            if ($isDue) {

                $expenses[] = [
                    'description' => $plannedBudget->description,
                    'amount' => (float) $detail->amount,
                    'date' => $currentDate->toDateString(),
                    'month_year' => $monthStart->toDateString(),
                    'day_due' => $currentDate->day,
                    'automatic_payment' => false,
                    'account_taken_from' => $detail->account_taken_from,
                    'notes' => $plannedBudget->notes,
                    'tithe' => false,
                    'income_id' => null,
                    'planned_budget_id' => $plannedBudget->id,
                    'planned_budget_detail_id' => $detail->id,
                    'is_weekly_budget' => false,
                    'user_id' => Auth::id(),
                ];
            }

            $currentDate->addDay();
        }
    }
}

}