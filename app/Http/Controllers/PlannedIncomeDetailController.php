<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlannedIncomeDetail;

class PlannedIncomeDetailController extends Controller
{
    public function store(Request $request)
    {
        //
    }


    public function update(Request $request, PlannedIncomeDetail $plannedincomedetail) {
        $plannedincomedetail->update([
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'day_of_week_for_weekly' => $request->day_of_week_for_weekly,
            'day_deposited_for_monthly' => $request->day_deposited_for_monthly,
            'tithe' => $request->tithe,
            'deposit_date_for_biweekly_quarterly_yearly_onetime' => $request->deposit_date_for_biweekly_quarterly_yearly_onetime,
            'effective_date' => $request->effective_date,
        ]);

        return back();
    }

}
