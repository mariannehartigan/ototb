<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PlannedIncome;
use App\Models\PlannedExpense;
use App\Models\Income;
use App\Models\Expense;

use Carbon\Carbon;
use App\Services\MakePlanService;

class HomeController extends Controller
{
  public function __construct(
      private MakePlanService $makePlanService
  ) {}

  public function index() {

    $service = app(MakePlanService::class);

    // $service = new MakePlanService();

    // $service->makePlan(Carbon::now());

    // if (!isPlanGenerated()->previousMonth) { $service->makePlan(Carbon::now()->subMonth()); }
    if (!isPlanGenerated()->currentMonth) { $service->makePlan(Carbon::now()); }
    // if (!isPlanGenerated()->nextMonth) { $service->makePlan(Carbon::now()->addMonth()); }

/*     if (!isPlanGenerated()->previousMonth) { makePlan(Carbon::now()->subMonth()); }
    if (!isPlanGenerated()->currentMonth) { makePlan(Carbon::now()); }
    if (!isPlanGenerated()->nextMonth) { makePlan(Carbon::now()->addMonth()); } */

/*     $incomesWithExpenses = Income::with(['expenses' => function ($query) {
      $query->orderBy('position');
    }])->get(); */

    $incomesWithExpenses = Income::with([
      'expenses' => function ($query) {
          $query->orderBy('position');
      }
    ])
    ->orderBy('date', 'asc')
    ->get();


// get $plannedFixedExpenses
$today = now()->toDateString();

$plannedFixedExpenses = PlannedExpense::with('plannedExpenseDetails')
    ->get()
    ->map(function ($expense) use ($today) {

        $details = $expense->plannedExpenseDetails;

        $validDetails = $details->filter(function ($detail) use ($today) {
            return $detail->effective_date <= $today;
        });

        $currentDetails = collect();

        if ($validDetails->isNotEmpty()) {
            $currentDetails->push(
                $validDetails->sortByDesc('effective_date')->first()
            );
        }

        $futureDetails = $details
            ->filter(function ($detail) use ($today) {
                return $detail->effective_date > $today;
            })
            ->sortBy('effective_date')
            ->values();

        $expense->setAttribute('current_details', $currentDetails->values());
        $expense->setAttribute('future_details', $futureDetails);

        return $expense;
    });
    
    

    return inertia('Home', [
      'settings' => Setting::all(),
      'plannedIncomes' => PlannedIncome::with('plannedIncomeDetails')->get(),
      'plannedFixedExpenses' => $plannedFixedExpenses,
      'incomes' => Income::all(),
      'expenses' => Expense::all(),
      'incomesWithExpenses' => $incomesWithExpenses,
    ]);
  }
}
