<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedIncome extends Model
{
    protected $fillable = ['description', 'start_date', 'end_date', 'user_id'];

    public function incomes() {
        return $this->hasMany(\App\Models\Income::class);
    }

    public function plannedIncomeDetails(): HasMany {
        return $this->hasMany(\App\Models\PlannedIncomeDetail::class);
    }
    
    public function plannedExpenses(): HasMany {
        return $this->hasMany(\App\Models\PlannedExpense::class);
    }

}
