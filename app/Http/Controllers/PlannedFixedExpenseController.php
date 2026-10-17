<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedExpense;
use App\Models\PlannedExpenseDetail;
use App\Models\Income;

use Carbon\Carbon;
use App\Services\MakePlanService;

class PlannedFixedExpenseController extends Controller
{
    public function __construct(
        private MakePlanService $makePlanService
    ) {}

    public function store(Request $request)
    {
        if ($request->type === 'addFutureChangeToCurrentFixedExpense') {

            // Use the existing PlannedFixedExpense
            $plannedExpense = PlannedExpense::findOrFail($request->plannedExpenseId);

            if ($request->frequency === 'monthly') {

                foreach ($request->monthlyPays as $pay) {

                    PlannedExpenseDetail::create([
                        'planned_expense_id' => $plannedExpense->id,
                        'planned_income_id' => $plannedExpense->plannedIncomeId,
                        'amount' => $pay['amount'],
                        'account_taken_from'    => $plannedExpense->accountTakenFrom,
                        'frequency' => $request->frequency,
                        'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                        'same_each_pay_for_monthly' => $request->sameEachPayForMonthly,
                        'day_due_for_monthly' => $pay['dayDueForMonthly'],
                        'effective_date' => $request->effectiveDate,
                        'tithe' => $request->tithe,
                    ]);
                }

            } else {

                PlannedExpenseDetail::create([
                    'planned_expense_id' => $plannedExpense->id,
                    'planned_income_id' => $plannedExpense->plannedIncomeId,
                    'amount' => $request->amount,
                    'account_taken_from'    => $plannedExpense->accountTakenFrom,
                    'frequency' => $request->frequency,
                    'day_of_week_for_weekly_biweekly' => $request->dayOfWeekForWeeklyBiweekly,
                    'due_date_for_biweekly_quarterly_yearly_onetime' =>
                        $request->dueDateForBiweeklyQuarterlyYearlyOnetime,
                    'tithe' => $request->tithe,
                    'effective_date' => $request->effectiveDate,
                ]);
            }

        } else {

            // NEW FixedExpense — create a brand-new PlannedFixedExpense
            if ($request->frequency === 'monthly') {

                foreach ($request->monthlyPays as $pay) {

                    $plannedExpense = PlannedExpense::create([
                        'description' => $request->description,
                        'start_date' => $request->startDate,
                        'end_date' => $request->endDate,
                        'notes' => $request->notes,
                        'user_id' => Auth::id(),
                    ]);

                    PlannedExpenseDetail::create([
                        'planned_expense_id' => $plannedExpense->id,
                        'planned_income_id' => $plannedExpense->plannedIncomeId,
                        'amount' => $pay['amount'],
                        'account_taken_from'    => $plannedExpense->accountTakenFrom,
                        'frequency' => $request->frequency,
                        'day_of_week_for_weekly_biweekly' => $request->dayOfWeekForWeeklyBiweekly,
                        'same_each_pay_for_monthly' => $request->sameEachPayForMonthly,
                        'day_due_for_monthly' => $pay['dayDueForMonthly'],
                        'tithe' => $request->tithe,
                        'effective_date' => $request->effectiveDate,
                    ]);
                }

            } else {

                $plannedExpense = PlannedExpense::create([
                    'description' => $request->description,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'notes' => $request->notes,
                    'user_id' => Auth::id(),
                ]);

                PlannedExpenseDetail::create([
                    'planned_expense_id' => $plannedExpense->id,
                    'planned_income_id' => $plannedExpense->plannedIncomeId,
                    'amount' => $request->amount,
                    'account_taken_from'    => $plannedExpense->accountTakenFrom,
                    'frequency' => $request->frequency,
                    'day_of_week_for_weekly_biweekly' => $request->dayOfWeekForWeeklyBiweekly,
                    'due_date_for_biweekly_quarterly_yearly_onetime' =>
                        $request->dueDateForBiweeklyQuarterlyYearlyOnetime,
                    'tithe' => $request->tithe,
                    'effective_date' => $request->effectiveDate,
                ]);
            }
        }

        $this->makePlanService->makePlan(
            Carbon::parse($request->effectiveDate)
        );

        return redirect('/');
    }


public function update(Request $request, PlannedExpense $plannedexpense)
{
    $plannedexpense->update([
        'description' => $request->description,
        'start_date' => $request->startDate,
        'end_date' => $request->endDate,
        'notes' => $request->notes,
    ]);

    foreach ($request->details as $detailData) {

        $detail = $plannedexpense->plannedExpenseDetails()
            ->find($detailData['id']);

        if ($detail) {
            $detail->update([
                'amount' => $detailData['amount'],
                'frequency' => $detailData['frequency'],
                'effective_date' => $detailData['effective_date'],
                'day_of_week_for_weekly' => $detailData['day_of_week_for_weekly'],
                'day_deposited_for_monthly' => $detailData['day_deposited_for_monthly'],
                'tithe' => $detailData['tithe'],
            ]);
        }
    }

        $this->makePlanService->makePlan(
            Carbon::parse($request->effectiveDate)
        );

    return back();
}

}
