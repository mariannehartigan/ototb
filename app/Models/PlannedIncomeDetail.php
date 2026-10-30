<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedIncomeDetail extends Model
{
    protected $fillable = ['planned_income_id', 'amount', 'frequency', 'day_of_week_for_weekly', 'day_deposited_for_monthly', 'deposit_date_for_biweekly_quarterly_yearly_onetime', 'effective_date', 'tithe', 'assign_no_expenses_except_tithe', 'notes',];

    protected $casts = [
        'tithe' => 'boolean',
        'assign_no_expenses_except_tithe' => 'boolean',
    ];

    public function plannedIncome(): BelongsTo {
        return $this->belongsTo(\App\Models\PlannedIncome::class);
    }
}
