<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedIncome;
use App\Models\PlannedIncomeDetail;
use App\Models\Income;

class InsertIncomesService {

    public function insertIncomes(Carbon $date) {
        $plannedIncomes = PlannedIncome::where('user_id', Auth::id())
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
                })
            ->with(['plannedIncomeDetails' => function ($query) use ($date) {
                $query->where('effective_date', '<=', $date);
            }])->get();

        foreach ($plannedIncomes as $plannedIncome) {
            $detail = $plannedIncome->plannedIncomeDetails->first();

            if (!$detail) {
                continue;
            }
            switch ($detail->frequency) {

                case 'weekly':
                    $this->insertWeeklyIncome($date, $plannedIncome);
                    break;

                case 'biweekly':
                    $this->insertBiweeklyIncome($date, $plannedIncome, $detail);
                    break;

                case 'monthly':
                    $this->insertMonthlyIncome($date, $plannedIncome, $detail);
                    break;

                case 'quarterly':
                    $this->insertQuarterlyIncome($date, $plannedIncome, $detail);
                    break;

                case 'yearly':
                    $this->insertYearlyIncome($date, $plannedIncome, $detail);
                    break;

                case 'onetime':
                    $this->insertOnetimeIncome($date, $plannedIncome, $detail);
                    break;
            }
        }
    }

    // WEEKLY
    private function insertWeeklyIncome(Carbon $date, PlannedIncome $plannedIncome): void
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        // Start at the Sunday of the week containing the 1st.
        $weekStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);

        while ($weekStart->lte($monthEnd)) {

            $weekEnd = $weekStart->copy()->addDays(6);

            // Check each day in this Sunday-to-Saturday week.
            for (
                $depositDate = $weekStart->copy();
                $depositDate->lte($weekEnd);
                $depositDate->addDay()
            ) {
                // Only generate incomes in the requested month.
                if (
                    $depositDate->lt($monthStart) ||
                    $depositDate->gt($monthEnd)
                ) {
                    continue;
                }

                // Find the detail effective on this date.
                $detail = $plannedIncome->plannedIncomeDetails()
                    ->whereDate('effective_date', '<=', $depositDate)
                    ->orderByDesc('effective_date')
                    ->orderByDesc('id')
                    ->first();

                if (!$detail) {
                    continue;
                }

                if (
                    $depositDate->englishDayOfWeek ===
                    $detail->day_of_week_for_weekly
                ) {
                    Income::create([
                        'planned_income_id' => $plannedIncome->id,
                        'date' => $depositDate->copy(),
                        'description' => $plannedIncome->description,
                        'amount' => $detail->amount,
                        'remaining' => $detail->amount,
                        'user_id' => Auth::id(),
                    ]);

                    // Stop after one deposit for this week.
                    break;
                }
            }

            $weekStart->addWeek();
        }
    }

/*     private function insertWeeklyIncome(Carbon $date, PlannedIncome $plannedIncome) {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $depositDate = $monthStart->copy();

        while ($depositDate->lte($monthEnd)) {

            // Find the detail that is effective on this date
            $detail = $plannedIncome->plannedIncomeDetails()
                ->whereDate('effective_date', '<=', $depositDate)
                ->orderByDesc('effective_date')
                ->first();

            if ($detail) {

                // Is this the correct day of the week?
                if ($depositDate->englishDayOfWeek === $detail->day_of_week_for_weekly) {

                    Income::create([
                        'planned_income_id' => $plannedIncome->id,
                        'date' => $depositDate->copy(),
                        'description' => $plannedIncome->description,
                        'amount' => $detail->amount,
                        'remaining' => $detail->amount,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            $depositDate->addDay();
        }
    } */

    // BIWEEKLY
    private function insertBiweeklyIncome(Carbon $date, PlannedIncome $plannedIncome): void
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $details = $plannedIncome->plannedIncomeDetails()
            ->whereDate('effective_date', '<=', $monthEnd)
            ->orderBy('effective_date')
            ->orderBy('id')
            ->get();

        foreach ($details as $index => $detail) {

            $effectiveDate = Carbon::parse($detail->effective_date);

            // This detail stops when the next one becomes effective.
            $nextDetail = $details->get($index + 1);

            $detailEnd = $nextDetail
                ? Carbon::parse($nextDetail->effective_date)->subDay()
                : $monthEnd->copy();

            $depositDate = $effectiveDate->copy();

            // Advance to the first deposit in the requested month.
            while ($depositDate->lt($monthStart)) {
                $depositDate->addWeeks(2);
            }

            while (
                $depositDate->lte($monthEnd) &&
                $depositDate->lte($detailEnd)
            ) {
                Income::create([
                    'planned_income_id' => $plannedIncome->id,
                    'date' => $depositDate->copy(),
                    'description' => $plannedIncome->description,
                    'amount' => $detail->amount,
                    'remaining' => $detail->amount,
                    'user_id' => Auth::id(),
                ]);

                $depositDate->addWeeks(2);
            }
        }
    }

/*     private function insertBiweeklyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
        $depositDate = Carbon::parse($detail->effective_date);

        while ($depositDate->lt($date->copy()->startOfMonth())) {
            $depositDate->addWeeks(2);
        }

        while (
            $depositDate->month === $date->month &&
            $depositDate->year === $date->year
        ) {
            Income::create([
                'planned_income_id' => $plannedIncome->id,
                'date' => $depositDate->copy(),
                'description' => $plannedIncome->description,
                'amount' => $detail->amount,
                'remaining' => $detail->amount,
                'user_id' => Auth::id(),
            ]);

            $depositDate->addWeeks(2);
        }
    } */

    // MONTHLY
    private function insertMonthlyIncome(Carbon $date, PlannedIncome $plannedIncome): void
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        // Check each day for the currently effective monthly schedule.
        for (
            $depositDate = $monthStart->copy();
            $depositDate->lte($monthEnd);
            $depositDate->addDay()
        ) {
            $detail = $plannedIncome->plannedIncomeDetails()
                ->whereDate('effective_date', '<=', $depositDate)
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->first();

            if (!$detail) {
                continue;
            }

            $day = min(
                (int) $detail->day_deposited_for_monthly,
                $date->daysInMonth
            );

            if ($depositDate->day === $day) {

                Income::create([
                    'planned_income_id' => $plannedIncome->id,
                    'date' => $depositDate->copy(),
                    'description' => $plannedIncome->description,
                    'amount' => $detail->amount,
                    'remaining' => $detail->amount,
                    'user_id' => Auth::id(),
                ]);

                // Only one monthly income per month.
                break;
            }
        }
    }

/*     private function insertMonthlyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
        $day = min(
            $detail->day_deposited_for_monthly,
            $date->daysInMonth
        ); // constrain to last day of month instead of 31st

        $depositDate = Carbon::create(
            $date->year,
            $date->month,
            $day
        );

        Income::create([
            'planned_income_id' => $plannedIncome->id,
            'date' => $depositDate,
            'description' => $plannedIncome->description,
            'amount' => $detail->amount,
            'remaining' => $detail->amount,
            'user_id' => Auth::id(),
        ]);
    } */

    // QUARTERLY
    private function insertQuarterlyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {

        $depositDate = Carbon::parse($detail->effective_date);

        while ($depositDate->lt($date->copy()->startOfMonth())) {
            $depositDate->addMonthsNoOverflow(3);
        }

        while (
            $depositDate->month === $date->month &&
            $depositDate->year === $date->year
        ) {
            Income::create([
                'planned_income_id' => $plannedIncome->id,
                'date' => $depositDate->copy(),
                'description' => $plannedIncome->description,
                'amount' => $detail->amount,
                'remaining' => $detail->amount,
                'user_id' => Auth::id(),
            ]);

            $depositDate->addMonthsNoOverflow(3);
        }
    }

    // YEARLY
    private function insertYearlyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
        $depositDate = Carbon::parse($detail->effective_date);
        while ($depositDate->lt($date->copy()->startOfMonth())) {
          $depositDate->addYearNoOverflow();
        }
        while ($depositDate->month === $date->month && $depositDate->year === $date->year) {
          \App\Models\Income::create([
            'planned_income_id' => $plannedIncome->id,
            'date' => $depositDate->copy(),
            'description' => $plannedIncome->description,
            'amount' => $detail->amount,
            'tithe' => $detail->tithe,
            'user_id' => Auth::id(),
            ]);
          $depositDate->addYearNoOverflow();
        }      
    }
    
    // ONETIME
    private function insertOnetimeIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
        $depositDate = Carbon::parse($detail->effective_date);
        if ($depositDate->month === $date->month && $depositDate->year === $date->year) {
            \App\Models\Income::create([
                'planned_income_id' => $plannedIncome->id,
                'date' => $depositDate,
                'description' => $plannedIncome->description,
                'amount' => $detail->amount,
                'tithe' => $detail->tithe,
                'user_id' => Auth::id(),
            ]);
        }      
    }
}