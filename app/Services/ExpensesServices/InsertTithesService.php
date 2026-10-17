<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Models\Expense;
use App\Services\ExpensesServices\AllocateExpensesService;

class InsertTithesService
{
    public function __construct(
    private AllocateExpensesService $allocateExpensesService
) {}

    public function insertTithes(Carbon $date, Collection $incomes): void
    {
        $endOfMonth = $date->copy()->endOfMonth();
        $titheExpenses = PlannedExpense::where('user_id', Auth::id())
            ->where('tithe', true)
            ->with([
                'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                    $query
                        ->whereDate('effective_date', '<=', $endOfMonth)
                        ->orderBy('effective_date');
                }
            ])
            ->get();

        foreach ($titheExpenses as $titheExpense) {
            $this->allocateExpensesService->allocateExpense($titheExpense, $incomes, $date);
        }
        

/*         foreach($incomes as $income) {
            Expense::create([
                'description' => 'tithe',
                'amount' => $income->amount * 0.1,
                'date' => $income->date,
                'fixed_or_variable' => null,
                'manual_or_automatic' => null,
                'tithe' => true,
                'account_taken_from' => null,
                'notes' => null,
                'income_id' => $income->id,
                'user_id' => $income->user_id,
            ]);
        } */
    }
}