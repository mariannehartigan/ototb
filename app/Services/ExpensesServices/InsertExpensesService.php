<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Models\Expense;
// use App\Services\ExpensesServices\InsertExpenseNoIncomeIdService;

class InsertExpensesService
{
/*     public function __construct(
    private InsertExpenseNoIncomeIdService $insertExpenseNoIncomeIdService
) {} */

    public function insertExpenses(Carbon $date, Collection $incomes): void
    {
        $endOfMonth = $date->copy()->endOfMonth();
        $expenses = PlannedExpense::where('user_id', Auth::id())
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

        foreach ($expenses as $expense) {
            $detail = $expense->plannedExpenseDetails->last();

            if (!$detail) {continue;}

/*             $this->insertExpenseNoIncomeIdService->insertExpenseNoIncomeId($date, $expense, $detail); */

            Expense::create([
                'description' => $expense->description,
                'amount' => $detail->amount,
                'date' => $date,
                'fixed_or_variable' => $detail->fixed_or_variable,
                'manual_or_automatic' => $detail->manual_or_automatic,
                'tithe' => true,
                'account_taken_from' => $detail->account_taken_from,
                'notes' => $expense->notes,
                'income_id' => null,
                'user_id' => Auth::id(),
            ]);

        }
    }
}