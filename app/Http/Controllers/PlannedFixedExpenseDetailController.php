<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlannedExpenseDetail;

class PlannedFixedExpenseDetailController extends Controller
{
    public function store(Request $request)
    {
        //
    }


    public function update(Request $request, PlannedExpenseDetail $plannedfixedexpensedetail) {
        $plannedfixedexpensedetail->update([
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'day_of_week_for_weekly_biweekly' => $request->day_of_week_for_weekly,
            'day_due_for_monthly' => $request->day_due_for_monthly,
            'account_taken_from' => $request->account_taken_from,
            'planned_income_id' => $request->planned_income_id,
            'deposit_date_for_biweekly_quarterly_yearly_onetime' => $request->deposit_date_for_biweekly_quarterly_yearly_onetime,
            'effective_date' => $request->effective_date,
        ]);

        return back();
    }
}