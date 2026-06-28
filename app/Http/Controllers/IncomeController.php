<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;

class IncomeController extends Controller
{
    public function store(Request $request, income $income)
    {
        // maybe don't need income $income here       
    }

    public function update(Request $request, income $income)
    {
        $income->update($request->all());
        return back();
    }

    public function destroy(string $id)
    {
        //
    }
}
