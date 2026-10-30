<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedExpense extends Model
{
    protected $fillable = ['description', 'start_date', 'end_date', 'tithe', 'notes'];

    protected $casts = ['tithe' => 'boolean',];    

    public function plannedExpenseDetails(): HasMany {
        return $this->hasMany(\App\Models\PlannedExpenseDetail::class)
            ->orderBy('day_due_for_monthly');
    }

    public function expenses() {
        return $this->hasMany(Expense::class);
    }

    public function plannedIncome(): BelongsTo {
        return $this->belongsTo(\App\Models\PlannedIncome::class);
    }
}
