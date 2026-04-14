<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailMovementType extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function emailRequests(): HasMany
    {
        return $this->hasMany(EmailRequest::class);
    }
}