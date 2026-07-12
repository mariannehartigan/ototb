<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Income extends Model
{
    protected $fillable = ['date', 'description', 'amount', 'remaining', 'received', 'user_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function expenses(): HasMany {
        return $this->hasMany(\App\Models\Expense::class);
    } 
}
