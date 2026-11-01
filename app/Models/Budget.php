<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = ['description', 'amount', 'frequency'];
    
    public function plannedBudget() {
        return $this->belongsTo(\App\Models\PlannedBudget::class);
    }

    public function income(): BelongsTo {
        return $this->belongsTo(\App\Models\Income::class);
    }
}
