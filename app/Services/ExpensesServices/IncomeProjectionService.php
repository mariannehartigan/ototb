<?php

namespace App\Services;

use App\Models\PlannedIncome;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class IncomeProjectionService
{
    public function project(Carbon $start, int $months = 24): Collection
    {
        $startDate = $start->copy()->startOfMonth();
        $endDate = $startDate->copy()
            ->addMonthsNoOverflow($months)
            ->subDay();

        $plannedIncomes = PlannedIncome::where('user_id', Auth::id())
            ->with(['plannedIncomeDetails' => function ($query) use ($endDate) {
                $query->where(function ($query) use ($endDate) {
                    $query->whereDate('effective_date', '<=', $endDate)
                        ->orWhereNull('effective_date');
                })
                ->orderBy('effective_date')
                ->orderBy('id');
            }])
            ->get();

        $occurrences = collect();

        foreach ($plannedIncomes as $plannedIncome) {
            $details = $plannedIncome->plannedIncomeDetails;

            if ($details->isEmpty()) {
                continue;
            }

            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                if (!$this->isActive($plannedIncome, $date)) {
                    $date->addDay();
                    continue;
                }

                $activeDetails = $details->filter(function ($detail) use ($date) {
                    return $detail->effective_date === null
                        || Carbon::parse($detail->effective_date)->lte($date);
                });

                if ($activeDetails->isEmpty()) {
                    $date->addDay();
                    continue;
                }

                // Latest effective-date group. Multiple monthly rows
                // may represent multiple deposits in that month.
                $latestEffectiveDate = $activeDetails->last()->effective_date;

                $currentDetails = $activeDetails->filter(
                    fn ($detail) =>
                        $detail->effective_date == $latestEffectiveDate
                );

                foreach ($currentDetails as $detail) {
                    if (!$this->isDepositDate($date, $detail)) {
                        continue;
                    }

                    $protected = (bool)
                        $detail->assign_no_expenses_except_tithe;

                    $frequency = $detail->frequency;

                    $occurrences->push((object) [
                        'id' => 'income-' . $plannedIncome->id
                            . '-' . $detail->id
                            . '-' . $date->format('Y-m-d'),
                        'planned_income_id' => $plannedIncome->id,
                        'planned_income_detail_id' => $detail->id,
                        'description' => $plannedIncome->description,
                        'date' => $date->toDateString(),
                        'amount' => (float) $detail->amount,
                        'remaining' => (float) $detail->amount,
                        'frequency' => $frequency,
                        'tithe' => (bool) $detail->tithe,
                        'assign_no_expenses_except_tithe' => $protected,
                        'classification' => $protected
                            ? 'protected'
                            : (
                                in_array(
                                    $frequency,
                                    ['quarterly', 'yearly', 'onetime'],
                                    true
                                )
                                ? 'backup'
                                : 'regular'
                            ),
                    ]);
                }

                $date->addDay();
            }
        }

        return $occurrences
            ->sortBy([
                ['date', 'asc'],
                ['planned_income_id', 'asc'],
            ])
            ->values();
    }

    private function isActive(PlannedIncome $income, Carbon $date): bool
    {
        if (
            $income->start_date &&
            $date->lt(Carbon::parse($income->start_date))
        ) {
            return false;
        }

        if (
            $income->end_date &&
            $date->gt(Carbon::parse($income->end_date))
        ) {
            return false;
        }

        return true;
    }

    private function isDepositDate(Carbon $date, $detail): bool
    {
        switch ($detail->frequency) {
            case 'weekly':
                return $date->englishDayOfWeek ===
                    $detail->day_of_week_for_weekly;

            case 'biweekly':
                return $this->matchesBiweekly($date, $detail);

            case 'monthly':
                if (!$detail->day_deposited_for_monthly) {
                    return false;
                }

                return $date->day === min(
                    (int) $detail->day_deposited_for_monthly,
                    $date->daysInMonth
                );

            case 'quarterly':
                return $this->matchesMonthlyInterval($date, $detail, 3);

            case 'yearly':
                return $this->matchesMonthlyInterval($date, $detail, 12);

            case 'onetime':
                return $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
                    && $date->isSameDay(Carbon::parse(
                        $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
                    ));

            default:
                return false;
        }
    }

    private function matchesBiweekly(Carbon $date, $detail): bool
    {
        if (!$detail->deposit_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
        );

        if ($date->lt($anchor)) {
            return false;
        }

        return $anchor->diffInDays($date) % 14 === 0;
    }

    private function matchesMonthlyInterval(
        Carbon $date,
        $detail,
        int $interval
    ): bool {
        if (!$detail->deposit_date_for_biweekly_quarterly_yearly_onetime) {
            return false;
        }

        $anchor = Carbon::parse(
            $detail->deposit_date_for_biweekly_quarterly_yearly_onetime
        );

        if ($date->lt($anchor)) {
            return false;
        }

        $months = ($date->year - $anchor->year) * 12
            + ($date->month - $anchor->month);

        if ($months < 0 || $months % $interval !== 0) {
            return false;
        }

        // Preserve the original anchor day, including
        // end-of-month adjustments.
        return $date->day === min(
            $anchor->day,
            $date->daysInMonth
        );
    }
}
