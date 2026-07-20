<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['description', 'amount', 'frequency', 'date_available', 'user_id'];
}
