<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedExpenseDetail extends Model
{
    protected $fillable = ['planned_expense_id', 'planned_income_id', 'frequency', 'amount', 'account_taken_from', 'fixed_or_variable', 'manual_or_automatic', 'is_five_budget_month_savings', 'day_of_week_for_weekly_biweekly', 'day_due_for_monthly', 'due_date_for_biweekly_quarterly_yearly_onetime', 'effective_date', 'tithe'];

    public function plannedExpense(): BelongsTo {
        return $this->belongsTo(\App\Models\PlannedExpense::class);
    }
}
