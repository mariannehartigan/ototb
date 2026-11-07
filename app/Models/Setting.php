<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    public function settingChanges(): HasMany {
        return $this->hasMany(\App\Models\SettingChange::class);
    }
}