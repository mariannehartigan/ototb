<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedBudget extends Model
{
    protected $fillable = ['description', 'is_five_budget_month_savings', 'tithe', 'notes'];

    protected $casts = ['tithe' => 'boolean',];  

    public function plannedBudgetDetails(): HasMany {
        return $this->hasMany(\App\Models\PlannedBudgetDetail::class);
    }

    public function budgets() {
        return $this->hasMany(\App\Models\Budget::class);
    }

    public function plannedIncome(): BelongsTo {
        return $this->belongsTo(\App\Models\PlannedIncome::class);
    }
}
