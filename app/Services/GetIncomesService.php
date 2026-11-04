<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedIncome;

class GetIncomesService
{
    public function getIncomes(Carbon $date, bool $useWorstCaseDates = false
): array
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $plannedIncomes = PlannedIncome::with([
            'plannedIncomeDetails' => function ($query) use ($monthEnd) {
                $query->whereDate('effective_date', '<=', $monthEnd)
                    ->orderBy('effective_date')
                    ->orderBy('id');
            }
        ])
            ->where('user_id', Auth::id())
            ->whereDate('start_date', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $monthStart);
            })
            ->get();

        $incomes = [];

        foreach ($plannedIncomes as $plannedIncome) {

            $weeklyWeeks = [];
            $monthlyGenerated = false;

            for (
                $depositDate = $monthStart->copy();
                $depositDate->lte($monthEnd);
                $depositDate->addDay()
            ) {
                // Respect the planned income's start and end dates.
                if (
                    $depositDate->lt(Carbon::parse($plannedIncome->start_date)) ||
                    (
                        $plannedIncome->end_date &&
                        $depositDate->gt(Carbon::parse($plannedIncome->end_date))
                    )
                ) {
                    continue;
                }

                // Get details effective on this particular date.
                $details = $plannedIncome->plannedIncomeDetails
                    ->filter(function ($detail) use ($depositDate) {
                        return Carbon::parse($detail->effective_date)
                            ->startOfDay()
                            ->lte($depositDate);
                    });

                if ($details->isEmpty()) {
                    continue;
                }

                // Use the latest effective-date group.
                // This supports multiple monthly paydays.
                $latestEffectiveDate = $details
                    ->max('effective_date');

                $currentDetails = $details
                    ->filter(fn ($detail) =>
                        $detail->effective_date == $latestEffectiveDate
                    );

                foreach ($currentDetails as $detail) {

                    $shouldGenerate = false;

                    switch ($detail->frequency) {

                        case 'weekly':

                            if ($useWorstCaseDates) {

                                $shouldGenerate = in_array(
                                    $depositDate->day,
                                    [7, 14, 21, 28]
                                );

                            } else {

                                $weekStart = $depositDate->copy()
                                    ->startOfWeek(Carbon::SUNDAY)
                                    ->toDateString();

                                if (
                                    !isset($weeklyWeeks[$weekStart]) &&
                                    $depositDate->englishDayOfWeek ===
                                        $detail->day_of_week_for_weekly
                                ) {
                                    $shouldGenerate = true;
                                    $weeklyWeeks[$weekStart] = true;
                                }
                            }

                            break;

                        case 'biweekly':

                            if ($useWorstCaseDates) {

                                $shouldGenerate = in_array(
                                    $depositDate->day,
                                    [14, 28]
                                );

                            } else {

                                $anchor = Carbon::parse(
                                    $detail->effective_date
                                )->startOfDay();

                                $days = (int) $anchor->diffInDays(
                                    $depositDate,
                                    false
                                );

                                $shouldGenerate =
                                    $days >= 0 && $days % 14 === 0;
                            }

                            break;

                        case 'monthly':

                            $day = min(
                                (int) $detail->day_deposited_for_monthly,
                                $depositDate->daysInMonth
                            );

                            if (
                                !$monthlyGenerated &&
                                $depositDate->day === $day
                            ) {
                                $shouldGenerate = true;
                            }

                            break;

                        case 'quarterly':

                            $shouldGenerate = $this->isRecurringDate(
                                $depositDate,
                                Carbon::parse($detail->effective_date),
                                3
                            );

                            break;

                        case 'yearly':

                            $shouldGenerate = $this->isRecurringDate(
                                $depositDate,
                                Carbon::parse($detail->effective_date),
                                12
                            );

                            break;

                        case 'onetime':

                            $shouldGenerate = $depositDate->isSameDay(
                                Carbon::parse($detail->effective_date)
                            );

                            break;
                    }

                    if (!$shouldGenerate) {
                        continue;
                    }

                    $incomes[] = [
                        'planned_income_id' => $plannedIncome->id,
                        'planned_income_detail_id' => $detail->id,
                        'date' => $depositDate->toDateString(),
                        'description' => $plannedIncome->description,
                        'frequency' => $detail->frequency,
                        'amount' => $detail->amount,
                        'remaining' => $detail->amount,
                        'user_id' => Auth::id(),
                    ];

                    // Allow multiple monthly pays on the same
                    // effective schedule, but only one monthly
                    // payment cycle per month.
                    if ($detail->frequency === 'monthly') {
                        // Set after processing all monthly details,
                        // not here, so multiple paydays can work.
                    }
                }
            }
        }

        usort($incomes, fn ($a, $b) =>
            strcmp($a['date'], $b['date'])
        );

        return $incomes;
    }

    private function isRecurringDate(
        Carbon $date,
        Carbon $anchor,
        int $intervalMonths
    ): bool {
        $anchor = $anchor->copy()->startOfDay();

        if ($date->lt($anchor)) {
            return false;
        }

        $months =
            ($date->year - $anchor->year) * 12 +
            ($date->month - $anchor->month);

        if ($months % $intervalMonths !== 0) {
            return false;
        }

        return $anchor
            ->addMonthsNoOverflow($months)
            ->isSameDay($date);
    }
}
