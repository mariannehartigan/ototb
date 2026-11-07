<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingChange extends Model
{
    protected $fillable = ['tithe', 'budget_day_of_week', 'budget_type'];

    protected $casts = ['tithe' => 'boolean',];    

    public function setting(): BelongsTo {
        return $this->belongsTo(\App\Models\Setting::class);
    }
}