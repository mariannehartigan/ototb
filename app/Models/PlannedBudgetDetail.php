<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedBudgetDetail extends Model
{
    protected $fillable = ['planned_budget_id', 'planned_income_id', 'frequency', 'amount', 'account_taken_from', 'day_due_for_monthly', 'due_date_for_biweekly_quarterly_yearly_onetime', 'effective_date', 'last_date_before_change', 'first_date_of_change', 'tithe'];

    public function plannedBudget(): BelongsTo {
        return $this->belongsTo(\App\Models\PlannedBudget::class);
    }
}
