<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRequest extends Model
{
    use HasResources;

    protected $fillable = [
        'name',
        'email',
        'email_cargo_id',
        'email_movement_type_id',
        'created_by',
    ];

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
}