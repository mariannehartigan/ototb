<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['description', 'tithe', 'automatically_generated_tithe', 'amount', 'date', 'frequency', 'manual_or_automatic', 'account_taken_from', 'notes', 'paid', 'income_id', 'user_id'];
    
    public function income(): BelongsTo {
        return $this->belongsTo(\App\Models\Income::class);
    }
}
