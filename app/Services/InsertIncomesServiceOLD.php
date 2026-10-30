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
    private function insertWeeklyIncome(Carbon $date, PlannedIncome $plannedIncome) {
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
    }

    // BIWEEKLY
    private function insertBiweeklyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
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
    }

    // MONTHLY
    private function insertMonthlyIncome(Carbon $date, PlannedIncome $plannedIncome, PlannedIncomeDetail $detail) {
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
    }

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