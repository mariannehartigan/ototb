<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlannedIncome;

class PlannedIncomeController extends Controller
{
    public function store(Request $request)
    {
        //      
    }

    public function update(Request $request, plannedIncome $income)
    {
        $income->update($request->all());
        return back();
    }

    public function destroy(string $id)
    {
        //
    }
}
