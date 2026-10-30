
<?php

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

function isPlanGenerated()
{
    $now = Carbon::now();

    $planExists = function (Carbon $date) {
        return Income::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->exists();
    };

    return (object) [
        'previousMonth' => $planExists($now->copy()->subMonth()),
        'currentMonth' => $planExists($now->copy()),
        'nextMonth' => $planExists($now->copy()->addMonth()),
    ];
}
