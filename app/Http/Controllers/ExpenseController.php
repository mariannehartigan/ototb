<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, expense $expense)
    {
        $expense->update($request->all());
        return back();
    }

    public function destroy(string $id)
    {
        //
    }

    public function reorder(Request $request) {
      foreach ($request->input('expenses') as $expenseData) {
        Expense::where('id', $expenseData['id'])->update([
          'position' => $expenseData['position'],
          'income_id' => $expenseData['income_id']
          ]);
      }
      return back();
    }
}
