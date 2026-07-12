<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedIncome extends Model
{
    protected $fillable = ['description', 'amount', 'day_deposited', 'frequency', 'notes'];
}
