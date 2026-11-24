<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PlannedIncome;
use App\Models\PlannedExpense;
use App\Models\PlannedBudget;
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

    $plannedBudgets = PlannedBudget::with('plannedBudgetDetails')
        ->get()
        ->map(function ($budget) use ($today) {

            $details = $budget->plannedBudgetDetails;

            $validDetails = $details->filter(function ($detail) use ($today) {
                return $detail->first_date_of_change === null ||
                    $detail->first_date_of_change <= $today;
            });

            $currentDetails = collect();

            if ($validDetails->isNotEmpty()) {
                $currentDetails->push(
                    $validDetails->sortByDesc('first_date_of_change')->first()
                );
            }

            $futureDetails = $details
                ->filter(function ($detail) use ($today) {
                    return $detail->first_date_of_change !== null &&
                        $detail->first_date_of_change > $today;
                })
                ->sortBy('first_date_of_change')
                ->values();

            $budget->setAttribute('current_details', $currentDetails->values());
            $budget->setAttribute('future_details', $futureDetails);

            return $budget;
        });

    $plannedBudgetsWeekly = $plannedBudgets
        ->filter(fn ($budget) => $budget->current_details->first()?->frequency === 'weekly')
        ->values();

    $plannedBudgetsMonthly = $plannedBudgets
        ->filter(fn ($budget) => $budget->current_details->first()?->frequency === 'monthly')
        ->values();

    $plannedBudgetsQuarterly = $plannedBudgets
        ->filter(fn ($budget) => $budget->current_details->first()?->frequency === 'quarterly')
        ->values();

    $plannedBudgetsYearly = $plannedBudgets
        ->filter(fn ($budget) => $budget->current_details->first()?->frequency === 'yearly')
        ->values();

    $plannedBudgetsOneTime = $plannedBudgets
        ->filter(fn ($budget) => $budget->current_details->first()?->frequency === 'onetime')
        ->values();

    
    return inertia('Home', [
      'settings' => Setting::with('settingChanges')->get(),
      'plannedIncomes' => PlannedIncome::with('plannedIncomeDetails')->get(),
      'plannedFixedExpenses' => $plannedFixedExpenses,
      'plannedBudgetsWeekly' => $plannedBudgetsWeekly,
      'plannedBudgetsMonthly' => $plannedBudgetsMonthly,
      'plannedBudgetsQuarterly' => $plannedBudgetsQuarterly,
      'plannedBudgetsYearly' => $plannedBudgetsYearly,
      'plannedBudgetsOneTime' => $plannedBudgetsOneTime,
      'incomes' => Income::all(),
      'expenses' => Expense::all(),
      'incomesWithExpenses' => $incomesWithExpenses,
    ]);
  }
}
