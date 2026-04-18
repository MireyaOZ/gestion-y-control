<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailRequest extends Model
{
    use HasResources;
    use SoftDeletes;

    protected $appends = [
        'operational_status',
        'operational_status_tone',
    ];

    protected $fillable = [
        'request_date',
        'name',
        'email',
        'email_cargo_id',
        'email_movement_type_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
        ];
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(EmailCargo::class, 'email_cargo_id');
    }

    public function movementType(): BelongsTo
    {
        return $this->belongsTo(EmailMovementType::class, 'email_movement_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOperationalStatusAttribute(): string
    {
        return $this->movementType?->slug === 'baja' ? 'Inactivo' : 'Activo';
    }

    public function getOperationalStatusToneAttribute(): string
    {
        return $this->movementType?->slug === 'baja' ? 'rechazado' : 'completada';
    }
}