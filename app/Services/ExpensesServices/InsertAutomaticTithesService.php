<?php

namespace App\Services\ExpensesServices;

use App\Models\Expense;
use App\Models\Income;
use App\Models\PlannedIncomeDetail;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InsertAutomaticTithesService
{
    public function insertAutomaticTithes(
        Carbon $date,
        Collection $incomes
    ): void {
        $userId = Auth::id();

        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        DB::transaction(function () use (
            $userId,
            $monthStart,
            $monthEnd
        ) {
            $settings = Setting::where('user_id', $userId)->first();

            // Global tithe setting is the master switch.
            if (!$settings || !$settings->tithe) {
                return;
            }

            $incomes = Income::where('user_id', $userId)
                ->whereBetween('date', [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ])
                ->orderBy('date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Determine which incomes are designated for tithing.
            $titheIncomes = $incomes->filter(function ($income) {
                if (!$income->planned_income_id) {
                    return false;
                }

                $detail = PlannedIncomeDetail::where(
                    'planned_income_id',
                    $income->planned_income_id
                )
                    ->whereDate('effective_date', '<=', $income->date)
                    ->orderByDesc('effective_date')
                    ->orderByDesc('id')
                    ->first();

                return $detail && (bool) $detail->tithe;
            })->values();

            // Calculate total tithe obligation for the month.
            $totalTitheOwed = $titheIncomes->sum(function ($income) {
                return round((float) $income->amount * 0.10, 2);
            });

            // Find manually entered tithe expenses for the month.
            $manualTitheTotal = Expense::where('user_id', $userId)
                ->where('tithe', true)
                ->where('automatically_generated_tithe', false)
                ->whereBetween('date', [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ])
                ->sum('amount');

            $remainingTitheOwed = max(
                0,
                round($totalTitheOwed - (float) $manualTitheTotal, 2)
            );

            // Remove previously generated automatic tithe expenses.
            // Restore the income balances before recalculating.
            $existingAutomaticTithes = Expense::where('user_id', $userId)
                ->where('automatically_generated_tithe', true)
                ->whereBetween('date', [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ])
                ->lockForUpdate()
                ->get();

            foreach ($existingAutomaticTithes as $expense) {
                if ($expense->income_id) {
                    $income = $incomes->firstWhere(
                        'id',
                        $expense->income_id
                    );

                    if ($income) {
                        $income->remaining = round(
                            (float) $income->remaining
                            + (float) $expense->amount,
                            2
                        );

                        $income->save();
                    }
                }

                $expense->delete();
            }

            // Generate automatic tithe only against incomes
            // explicitly designated for tithing.
            foreach ($titheIncomes as $income) {
                if ($remainingTitheOwed <= 0) {
                    break;
                }

                $incomeTithe = round(
                    (float) $income->amount * 0.10,
                    2
                );

                $automaticTithe = min(
                    $incomeTithe,
                    $remainingTitheOwed
                );

                if ($automaticTithe <= 0) {
                    continue;
                }

                Expense::create([
                    'description' => 'Tithe',
                    'amount' => $automaticTithe,
                    'date' => $income->date,
                    'month_year' => $monthStart->toDateString(),
                    'fixed_or_variable' => null,
                    'manual_or_automatic' => null,
                    'tithe' => true,
                    'automatically_generated_tithe' => true,
                    'paid' => false,
                    'account_taken_from' => null,
                    'notes' => null,
                    'income_id' => $income->id,
                    'planned_expense_id' => null,
                    'user_id' => $userId,
                ]);

                $income->remaining = round(
                    (float) $income->remaining - $automaticTithe,
                    2
                );

                $income->save();

                $remainingTitheOwed = round(
                    $remainingTitheOwed - $automaticTithe,
                    2
                );
            }
        });
    }
}
