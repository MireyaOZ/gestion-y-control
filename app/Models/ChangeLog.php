<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChangeLog extends Model
{
    private const ACTION_LABELS = [
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'deleted' => 'Eliminado',
        'assigned' => 'Asignado',
        'status_changed' => 'Estado actualizado',
    ];

    protected $fillable = [
        'action',
        'content',
        'author_id',
    ];

    protected $appends = [
        'localized_action',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getLocalizedActionAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? str($this->action)
            ->replace('_', ' ')
            ->headline()
            ->toString();
    }
}
