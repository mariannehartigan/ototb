<?php

use Carbon\Carbon;

function isPlanGenerated()
{
    $previousMonth = Carbon::now()->subMonth()->month;
    $currentMonth = Carbon::now()->month;
    $nextMonth = Carbon::now()->addMonth()->month;

    $previousMonthIncomesExist = App\Models\Income
        ::whereYear('date', now()->year)
        ->whereMonth('date', $previousMonth)
        ->exists();
    $currentMonthIncomesExist = App\Models\Income
        ::whereYear('date', now()->year)
        ->whereMonth('date', $currentMonth)
        ->exists();
    $nextMonthIncomesExist = App\Models\Income
        ::whereYear('date', now()->year)
        ->whereMonth('date', $nextMonth)
        ->exists();

    return (object) [
        'previousMonth' => $previousMonthIncomesExist,
        'currentMonth' => $currentMonthIncomesExist,
        'nextMonth' => $nextMonthIncomesExist,
    ];
}