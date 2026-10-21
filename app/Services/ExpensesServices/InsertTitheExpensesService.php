<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Services\ExpensesServices\InsertExpenseNoIncomeIdService;

class InsertTitheExpensesService
{
    public function __construct(
    private InsertExpenseNoIncomeIdService $insertExpenseNoIncomeIdService
) {}

    public function insertTitheExpenses(Carbon $date, Collection $incomes): void
    {
        $endOfMonth = $date->copy()->endOfMonth();
        $titheExpenses = PlannedExpense::where('user_id', Auth::id())
            // ->where('tithe', true)
            ->with([
                'plannedExpenseDetails' => function ($query) use ($endOfMonth) {
                    $query
                        ->whereDate('effective_date', '<=', $endOfMonth)
                        ->orderBy('effective_date');
                }
            ])
            ->get();

/*         foreach ($titheExpenses as $titheExpense) {
            $this->insertExpenseNoIncomeIdService->insertExpenseNoIncomeId($date, $titheExpense);
        } */

        foreach ($titheExpenses as $titheExpense) {
            $detail = $titheExpense->plannedExpenseDetails->last();

            if (!$detail) {continue;}

            $this->insertExpenseNoIncomeIdService->insertExpenseNoIncomeId($date, $titheExpense, $detail);
        }
    }
}