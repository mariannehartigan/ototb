<?php

namespace App\Services;

use App\Models\Income;
use App\Models\PlannedIncome;
use App\Models\PlannedIncomeDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InsertIncomesService
{
    public function insertIncomes(Carbon $date): void
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $plannedIncomes = PlannedIncome::where('user_id', Auth::id())
            ->where(function ($query) use ($monthEnd) {
                $query->whereDate('start_date', '<=', $monthEnd)
                    ->orWhereNull('start_date');
            })
            ->where(function ($query) use ($monthStart) {
                $query->whereDate('end_date', '>=', $monthStart)
                    ->orWhereNull('end_date');
            })
            ->with(['plannedIncomeDetails' => function ($query) use ($monthEnd) {
                $query->whereDate('effective_date', '<=', $monthEnd)
                    ->orderBy('effective_date')
                    ->orderBy('id');
            }])
            ->get();

        foreach ($plannedIncomes as $plannedIncome) {
            $details = $plannedIncome->plannedIncomeDetails;

            if ($details->isEmpty()) {
                continue;
            }

            $startDate = $plannedIncome->start_date
                ? Carbon::parse($plannedIncome->start_date)
                : $monthStart->copy();

            $endDate = $plannedIncome->end_date
                ? Carbon::parse($plannedIncome->end_date)
                : $monthEnd->copy();

            $firstDate = $startDate->greaterThan($monthStart)
                ? $startDate
                : $monthStart;

            $lastDate = $endDate->lessThan($monthEnd)
                ? $endDate
                : $monthEnd;

            if ($firstDate->greaterThan($lastDate)) {
                continue;
            }

            $this->generateIncomeDates(
                $plannedIncome,
                $firstDate,
                $lastDate
            );
        }
    }

    private function generateIncomeDates(
        PlannedIncome $plannedIncome,
        Carbon $firstDate,
        Carbon $lastDate
    ): void {
        $details = $plannedIncome->plannedIncomeDetails;

        // Each calendar day is checked against the detail effective
        // on that day. This also handles changes within a month.
        $date = $firstDate->copy();

        while ($date->lessThanOrEqualTo($lastDate)) {
            $detail = $details
                ->filter(function ($item) use ($date) {
                    return $item->effective_date === null
                        || Carbon::parse($item->effective_date)
                            ->lessThanOrEqualTo($date);
                })
                ->last();

            if ($detail && $this->isDepositDate($date, $detail)) {
                $this->createIncome($plannedIncome, $detail, $date);
            }

            $date->addDay();
        }
    }

    private function isDepositDate(
        Carbon $date,
        PlannedIncomeDetail $detail
    ): bool {
        switch ($detail->frequency) {
            case 'weekly':
                return $date->englishDayOfWeek ===
                    $detail->day_of_week_for_weekly;

            case 'biweekly':
                return $this->matchesInterval($date, $detail, 'biweekly');

            case 'monthly':
                if (!$detail->day_deposited_for_monthly) {
                    return false;
                }

                return $date->day === min(
                    $detail->day_deposited_for_monthly,
                    $date->daysInMonth
                );

            case 'quarterly':
                return $this->matchesInterval($date, $detail, 'quarterly');

            case 'yearly':
                return $this->matchesInterval($date, $detail, 'yearly');

            case 'onetime':
                if (!$detail->deposit_date_for_biweekly_quarterly_yearly_onetime) {
                    return false;
                }

                return $date->isSameDay(
                    Carbon::parse(
                        $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
                    )
                );

            default:
                return false;
        }
    }

    private function matchesInterval(
        Carbon $date,
        PlannedIncomeDetail $detail,
        string $frequency
    ): bool {
        if (!$detail->deposit_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
        )->startOfDay();

        if ($date->lessThan($anchor)) {
            return false;
        }

        if ($frequency === 'biweekly') {
            return $anchor->diffInDays($date) % 14 === 0;
        }

        // Use the original anchor day, not a repeatedly shifted
        // date, so month-end dates do not drift.
        if ($frequency === 'quarterly') {
            $months = (
                ($date->year - $anchor->year) * 12
                + $date->month - $anchor->month
            );

            if ($months < 0 || $months % 3 !== 0) {
                return false;
            }

            return $date->day === min(
                $anchor->day,
                $date->daysInMonth
            );
        }

        if ($frequency === 'yearly') {
            return $date->month === $anchor->month
                && $date->day === min(
                    $anchor->day,
                    $date->daysInMonth
                );
        }

        return false;
    }

    private function createIncome(
        PlannedIncome $plannedIncome,
        PlannedIncomeDetail $detail,
        Carbon $date
    ): void {
        // Prevent generating the same planned income twice
        // on the same date.
        $exists = Income::where('user_id', Auth::id())
            ->where('planned_income_id', $plannedIncome->id)
            ->whereDate('date', $date->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        Income::create([
            'planned_income_id' => $plannedIncome->id,
            'date' => $date->toDateString(),
            'description' => $plannedIncome->description,
            'amount' => $detail->amount,
            'remaining' => $detail->amount,
            'received' => false,
            'user_id' => Auth::id(),
        ]);
    }
}
