<?php

namespace App\Services\GetExpensesService;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedExpense;

class GenerateBillsService
{
    public function generateBillsArray(Carbon $date, array &$expenses): void {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $plannedExpenses = PlannedExpense::with([
            'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                $query->whereDate('effective_date', '<=', $endOfMonth)
                    ->orderBy('effective_date')
                    ->orderBy('id');
            }
        ])
        ->where('user_id', Auth::id())
        ->whereDate('start_date', '<=', $endOfMonth)
        ->where(function ($query) use ($startOfMonth) {
            $query->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $startOfMonth);
        })
        ->get();

        foreach ($plannedExpenses as $plannedExpense) {

            $details = $plannedExpense->plannedExpenseDetails;

            if ($details->isEmpty()) {
                continue;
            }

            $dueDate = $startOfMonth->copy();

            while ($dueDate->lte($endOfMonth)) {

                if (
                    ($plannedExpense->start_date &&
                        $dueDate->lt(Carbon::parse($plannedExpense->start_date))) ||
                    ($plannedExpense->end_date &&
                        $dueDate->gt(Carbon::parse($plannedExpense->end_date)))
                ) {
                    $dueDate->addDay();
                    continue;
                }

                $detail = $details
                    ->filter(function ($item) use ($dueDate) {
                        return Carbon::parse($item->effective_date)
                            ->lte($dueDate);
                    })
                    ->last();

                if (!$detail) {
                    $dueDate->addDay();
                    continue;
                }

                $isDue = false;

                switch ($detail->frequency) {

                    case 'weekly':
                        $isDue = $dueDate->englishDayOfWeek ===
                            $detail->day_of_week_for_weekly_biweekly;
                        break;

                    case 'biweekly':
                        $isDue = $this->isRecurringDate(
                            $dueDate,
                            $detail->due_date_for_biweekly_quarterly_yearly_onetime,
                            14
                        );
                        break;

                    case 'monthly':
                        $day = min(
                            $detail->day_due_for_monthly,
                            $dueDate->daysInMonth
                        );

                        $isDue = $dueDate->day === $day;
                        break;

                    case 'quarterly':
                        $isDue = $this->isRecurringMonth(
                            $dueDate,
                            $detail->due_date_for_biweekly_quarterly_yearly_onetime,
                            3
                        );
                        break;

                    case 'yearly':
                        $isDue = $this->isRecurringMonth(
                            $dueDate,
                            $detail->due_date_for_biweekly_quarterly_yearly_onetime,
                            12
                        );
                        break;

                    case 'onetime':
                        $isDue = $detail->due_date_for_biweekly_quarterly_yearly_onetime &&
                            $dueDate->isSameDay(
                                Carbon::parse($detail->due_date_for_biweekly_quarterly_yearly_onetime)
                            );
                        break;
                }

                if ($isDue) {
                    $expenses[] = array_merge(
                        $plannedExpense->getAttributes(),
                        $detail->getAttributes(),
                        [
                            'id' => count($expenses) + 1,
                            'planned_expense_id' => $plannedExpense->id,
                            'planned_expense_detail_id' => $detail->id,
                            'date' => $dueDate->toDateString(),
                        ]
                    );
                }

                $dueDate->addDay();
            }
        }

        usort($expenses, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        dd($expenses);
    }

    // generateBillsArray subfunction
    private function isRecurringDate(Carbon $date, ?string $anchorDate, int $intervalDays): bool {
        if (!$anchorDate) {
            return false;
        }

        $anchor = Carbon::parse($anchorDate)->startOfDay();

        if ($date->lt($anchor)) {
            return false;
        }

        return $anchor->diffInDays($date->copy()->startOfDay()) %
            $intervalDays === 0;
    }

    // generateBillsArray subfunction
    private function isRecurringMonth(Carbon $date, ?string $anchorDate, int $intervalMonths): bool {
        if (!$anchorDate) {
            return false;
        }

        $anchor = Carbon::parse($anchorDate)->startOfDay();

        if ($date->lt($anchor)) {
            return false;
        }

        $monthsDifference =
            ($date->year - $anchor->year) * 12 +
            ($date->month - $anchor->month);

        if ($monthsDifference % $intervalMonths !== 0) {
            return false;
        }

        $expectedDate = $anchor->copy()
            ->addMonthsNoOverflow($monthsDifference);

        return $date->isSameDay($expectedDate);
    }

}