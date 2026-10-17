<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PlannedIncome;
use App\Models\PlannedIncomeDetail;
use App\Models\Income;

use Carbon\Carbon;
use App\Services\MakePlanService;

class PlannedIncomeController extends Controller
{
    public function __construct(
        private MakePlanService $makePlanService
    ) {}

    public function store(Request $request)
    {
        if ($request->type === 'addFutureChangeToCurrentIncome') {

            // Use the existing PlannedIncome
            $plannedIncome = PlannedIncome::findOrFail($request->plannedIncomeId);

            if ($request->frequency === 'monthly') {

                foreach ($request->monthlyPays as $pay) {

                    PlannedIncomeDetail::create([
                        'planned_income_id' => $plannedIncome->id,
                        'amount' => $pay['amount'],
                        'frequency' => $request->frequency,
                        'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                        'same_each_pay_for_monthly' => $request->sameEachPayForMonthly,
                        'day_deposited_for_monthly' => $pay['dayDepositedForMonthly'],
                        'effective_date' => $request->effectiveDate,
                        'tithe' => $request->tithe,
                    ]);
                }

            } else {

                PlannedIncomeDetail::create([
                    'planned_income_id' => $plannedIncome->id,
                    'amount' => $request->amount,
                    'frequency' => $request->frequency,
                    'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                    'deposit_date_for_biweekly_quarterly_yearly_onetime' =>
                        $request->depositDateForBiweeklyQuarterlyYearlyOnetime,
                    'tithe' => $request->tithe,
                    'effective_date' => $request->effectiveDate,
                ]);
            }

        } else {

            // NEW income — create a brand-new PlannedIncome
            if ($request->frequency === 'monthly') {

                foreach ($request->monthlyPays as $pay) {

                    $plannedIncome = PlannedIncome::create([
                        'description' => $request->description,
                        'start_date' => $request->startDate,
                        'end_date' => $request->endDate,
                        'notes' => $request->notes,
                        'user_id' => Auth::id(),
                    ]);

                    PlannedIncomeDetail::create([
                        'planned_income_id' => $plannedIncome->id,
                        'amount' => $pay['amount'],
                        'frequency' => $request->frequency,
                        'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                        'same_each_pay_for_monthly' => $request->sameEachPayForMonthly,
                        'day_deposited_for_monthly' => $pay['dayDepositedForMonthly'],
                        'effective_date' => $request->effectiveDate,
                        'tithe' => $request->tithe,
                    ]);
                }

            } else {

                $plannedIncome = PlannedIncome::create([
                    'description' => $request->description,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'notes' => $request->notes,
                    'user_id' => Auth::id(),
                ]);

                PlannedIncomeDetail::create([
                    'planned_income_id' => $plannedIncome->id,
                    'amount' => $request->amount,
                    'frequency' => $request->frequency,
                    'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                    'deposit_date_for_biweekly_quarterly_yearly_onetime' =>
                        $request->depositDateForBiweeklyQuarterlyYearlyOnetime,
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
/*     public function store(Request $request)
    {
        if ($request->frequency === 'monthly') {
            foreach ($request->monthlyPays as $pay) {
                $plannedIncome = PlannedIncome::create([
                    'description' => $request->description,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'notes' => $request->notes,
                    'user_id' => Auth::id(),
                ]);

                PlannedIncomeDetail::create([
                    'planned_income_id' => $plannedIncome->id,
                    'amount' => $pay['amount'],
                    'frequency' => $request->frequency,
                    'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                    'same_each_pay_for_monthly' => $request->sameEachPayForMonthly,
                    'day_deposited_for_monthly' => $pay['dayDepositedForMonthly'],
                    'effective_date' => $request->effectiveDate,
                    'tithe' => $request->tithe,
                ]);
            }
        } else {

            $plannedIncome = PlannedIncome::create([
                'description' => $request->description,
                'start_date' => $request->startDate,
                'end_date' => $request->endDate,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            PlannedIncomeDetail::create([
                'planned_income_id' => $plannedIncome->id,
                'amount' => $request->amount,
                'frequency' => $request->frequency,
                'day_of_week_for_weekly' => $request->dayOfWeekForWeeklyBiweekly,
                'deposit_date_for_biweekly_quarterly_yearly_onetime' => $request->depositDateForBiweeklyQuarterlyYearlyOnetime,
                'tithe' => $request->tithe,
                'effective_date' => $request->effectiveDate,
            ]);
        }

/*         Income::where('received', false)->each(function ($income) {
            $income->expenses()->delete();
            $income->delete();
        }); */
/*
        $this->makePlanService->makePlan(
            Carbon::parse($request->effectiveDate)
        );

        return redirect('/');     
    } */

public function update(Request $request, PlannedIncome $plannedincome)
{
    $plannedincome->update([
        'description' => $request->description,
        'start_date' => $request->startDate,
        'end_date' => $request->endDate,
        'notes' => $request->notes,
    ]);

    foreach ($request->details as $detailData) {

        $detail = $plannedincome->plannedIncomeDetails()
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

/*         Income::where('received', false)->each(function ($income) {
            $income->expenses()->delete();
            $income->delete();
        }); */

        $this->makePlanService->makePlan(
            Carbon::parse($request->effectiveDate)
        );

    return back();
}

}
