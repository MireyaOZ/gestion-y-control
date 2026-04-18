<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemRecord extends Model
{
    use HasResources;
    use SoftDeletes;

    protected $table = 'systems';

    protected $fillable = [
        'request_date',
        'name',
        'trello_url',
        'system_status_id',
        'pending_errors',
        'errors_in_progress',
        'in_review',
        'finalized',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'pending_errors' => 'integer',
            'errors_in_progress' => 'integer',
            'in_review' => 'integer',
            'finalized' => 'integer',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'system_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}