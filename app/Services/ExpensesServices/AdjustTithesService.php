<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannedExpense;
use App\Models\Expense;

class AdjustTithesService
{
    public function adjustTithes(Carbon $date, Collection $incomes): void
    {
       //
    }
}