<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldHealthSnapshot extends Model
{
    protected $fillable = [
        'world_id',
        'health_status',
        'health_score',
        'tick',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'health_status' => \Tuzy\Domain\World\ValueObject\WorldHealthStatus::class,
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
