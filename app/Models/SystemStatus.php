<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemStatus extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function systems(): HasMany
    {
        return $this->hasMany(SystemRecord::class, 'system_status_id');
    }
}