<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PlannedIncome;
use App\Models\PlannedExpense;
use App\Models\Income;
use App\Models\Expense;

use Carbon\Carbon;

class HomeController extends Controller
{
  public function index() {

    if (!isPlanGenerated()->previousMonth) { makePlan(Carbon::now()->subMonth()); }
    if (!isPlanGenerated()->currentMonth) { makePlan(Carbon::now()); }
    if (!isPlanGenerated()->nextMonth) { makePlan(Carbon::now()->addMonth()); }

    // $incomesWithExpenses = Income::with(['expenses' => function ($query) {
    //   $query->orderBy('position');
    // }])->get();

    $incomesWithExpenses = Income::with([
      'expenses' => function ($query) {
          $query->orderBy('position');
      }
    ])
    ->orderBy('date', 'asc')
    ->get();

    return inertia('Home', [
      'settings' => Setting::all(),
      'plannedIncomes' => PlannedIncome::orderBy('day_deposited')->get(),
      'plannedExpenses' => PlannedExpense::orderBy('day_due')->get(),
      'incomes' => Income::all(),
      'expenses' => Expense::all(),
      'incomesWithExpenses' => $incomesWithExpenses,
    ]);
  }
}
