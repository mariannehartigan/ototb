<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Income;

class AddAutomaticTithesService
{
    public function addAutomaticTithes (Carbon $date, array $expenses) {

        $totalIncome = Income::where('user_id', Auth::id())
            ->whereBetween('date', [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth()
            ])
            ->sum('amount');

        // sum up all non-automatic tithes
        $totalTithe = 0;
        foreach ($expenses as $expense) {
            if ($expense['tithe'] ?? false) {
                $totalTithe += (float) $expense['amount'];
            } else {
                $totalTithe += (float) ($expense['sum_of_tithe_items'] ?? 0);
            }
        }
         dd($totalIncome, $totalTithe); //8800, 615

        if ($totalTithe >= $totalIncome * 0.1) { //205 
            dd('Tithes are already sufficient');
        } else {
          dd('Tithes are insufficient, adding automatic tithes');
        }

    }
}