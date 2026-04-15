<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCargo extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'parent_name',
    ];

    public function emailRequests(): HasMany
    {
        return $this->hasMany(EmailRequest::class);
    }
}