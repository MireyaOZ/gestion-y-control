<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeLog extends Model
{
    use SoftDeletes;

    private const ACTION_LABELS = [
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'deleted' => 'Eliminado',
        'assigned' => 'Asignado',
        'status_changed' => 'Estado actualizado',
        'attachment_added' => 'Adjunto agregado',
        'attachment_deleted' => 'Adjunto eliminado',
    ];

    protected $fillable = [
        'action',
        'content',
        'author_id',
    ];

    protected $appends = [
        'localized_action',
        'rendered_content',
        'report_content',
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

    public function getStatusGroupAttribute(): string
    {
        preg_match('/data-status-group="([^"]+)"/', $this->content, $matches);

        return $matches[1] ?? 'Sin estatus';
    }

    public function getRenderedContentAttribute(): string
    {
        return preg_replace([
            '/^<div data-status-group="[^"]+">/',
            '/<\/div>$/',
        ], '', $this->content) ?? $this->content;
    }

    public function getReportContentAttribute(): string
    {
        return preg_replace('/^\s*<p>.*?<\/p>\s*/s', '', $this->rendered_content, 1) ?? $this->rendered_content;
    }
}
