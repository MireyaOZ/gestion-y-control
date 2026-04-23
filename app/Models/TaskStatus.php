<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    protected $fillable = ['name', 'slug'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : mb_strtoupper($value, 'UTF-8'),
            set: fn (?string $value) => $value === null ? null : mb_strtoupper($value, 'UTF-8'),
        );
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }
}
