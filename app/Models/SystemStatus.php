<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemStatus extends Model
{
    protected $appends = [
        'display_name',
    ];

    protected $fillable = [
        'name',
        'slug',
    ];

    public function isTesting(): bool
    {
        return $this->slug === 'en-pruebas';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->isTesting()
            ? 'En pruebas internas'
            : $this->name;
    }

    public function systems(): HasMany
    {
        return $this->hasMany(SystemRecord::class, 'system_status_id');
    }
}