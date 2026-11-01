<?php

namespace App\Services;

use App\Models\Income;
use App\Models\PlannedIncome;
use App\Models\PlannedIncomeDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InsertIncomesService
{
    public function insertIncomes(Carbon $date): void
    {
        $this->generateIncomes(
            $date,
            function (
                PlannedIncome $plannedIncome,
                PlannedIncomeDetail $detail,
                Carbon $depositDate
            ) {
                $this->createIncome(
                    $plannedIncome,
                    $detail,
                    $depositDate
                );
            }
        );
    }

    public function projectIncomes(Carbon $date): Collection
    {
        $projections = collect();

        $this->generateIncomes(
            $date,
            function (
                PlannedIncome $plannedIncome,
                PlannedIncomeDetail $detail,
                Carbon $depositDate
            ) use ($projections) {

                $income = new Income([
                    'planned_income_id' => $plannedIncome->id,
                    'date' => $depositDate->toDateString(),
                    'description' => $plannedIncome->description,
                    'amount' => $detail->amount,
                    'remaining' => $detail->amount,
                    'received' => false,
                    'user_id' => Auth::id(),
                ]);

                // These properties are used by the distributor.
                $income->classification = in_array(
                    $detail->frequency,
                    ['quarterly', 'yearly', 'onetime'],
                    true
                ) ? 'backup' : 'regular';

                $income->tithe = (bool) $detail->tithe;

                $income->assign_no_expenses_except_tithe =
                    (bool) $detail->assign_no_expenses_except_tithe;

                // This is a projected record, not a saved income.
                $income->is_projection = true;

                $projections->push($income);
            }
        );

        return $projections;
    }

    private function generateIncomes(
        Carbon $date,
        callable $callback
    ): void {

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
            ->with('plannedIncomeDetails')
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
                ? $startDate->copy()
                : $monthStart->copy();

            $lastDate = $endDate->lessThan($monthEnd)
                ? $endDate->copy()
                : $monthEnd->copy();

            if ($firstDate->greaterThan($lastDate)) {
                continue;
            }

            $this->generateIncomeDates(
                $plannedIncome,
                $firstDate,
                $lastDate,
                $callback
            );
        }
    }

    private function generateIncomeDates(
        PlannedIncome $plannedIncome,
        Carbon $firstDate,
        Carbon $lastDate,
        callable $callback
    ): void {

        $details = $plannedIncome->plannedIncomeDetails
            ->sortBy([
                ['effective_date', 'asc'],
                ['id', 'asc'],
            ]);

        $date = $firstDate->copy();

        while ($date->lessThanOrEqualTo($lastDate)) {

            // Find the most recent effective date that applies.
            $applicableDetails = $details->filter(function ($detail) use ($date) {
                return $detail->effective_date === null
                    || Carbon::parse($detail->effective_date)
                        ->lessThanOrEqualTo($date);
            });

            if ($applicableDetails->isNotEmpty()) {

                $latestEffectiveDate = $applicableDetails
                    ->max('effective_date');

                // Keep all details belonging to the latest change.
                // This supports multiple monthly paydays.
                $currentDetails = $applicableDetails->filter(
                    fn ($detail) =>
                        $detail->effective_date == $latestEffectiveDate
                );

                foreach ($currentDetails as $detail) {

                    if ($this->isDepositDate($date, $detail)) {

                        $callback(
                            $plannedIncome,
                            $detail,
                            $date->copy()
                        );
                    }
                }
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
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'biweekly'
                );

            case 'monthly':
                if (!$detail->day_deposited_for_monthly) {
                    return false;
                }

                return $date->day === min(
                    (int) $detail->day_deposited_for_monthly,
                    $date->daysInMonth
                );

            case 'quarterly':
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'quarterly'
                );

            case 'yearly':
                return $this->matchesInterval(
                    $date,
                    $detail,
                    'yearly'
                );

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
            return (int) $anchor->diffInDays($date) % 14 === 0;
        }

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

        // Avoid creating the same planned income occurrence twice.
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
