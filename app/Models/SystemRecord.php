<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemRecord extends Model
{
    use HasResources;

    protected $table = 'systems';

    protected $fillable = [
        'name',
        'trello_url',
        'system_status_id',
        'created_by',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'system_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}