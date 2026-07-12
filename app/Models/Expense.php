<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['description', 'tithe', 'amount', 'date', 'automatic_payment', 'account_taken_from', 'notes', 'paid', 'income_id', 'user_id'];
    
    public function income(): BelongsTo {
        return $this->belongsTo(\App\Models\Income::class);
    }
}
