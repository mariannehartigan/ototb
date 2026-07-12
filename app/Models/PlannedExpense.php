<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedExpense extends Model
{
    protected $fillable = ['description', 'tithe', 'amount', 'frequency', 'day_due', 'automatic_payment', 'account_taken_from', 'notes'];
}
