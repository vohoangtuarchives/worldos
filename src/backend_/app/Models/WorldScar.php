<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldScar extends Model
{
    protected $fillable = [
        'world_id',
        'source_event_id',
        'weight',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function sourceEvent(): BelongsTo
    {
        return $this->belongsTo(WorldEvent::class, 'source_event_id');
    }

    /**
     * Enforce Immutability: WorldScar is a permanent consequence.
     * It cannot be healed or forgotten.
     */
    protected static function booted(): void
    {
        static::updating(fn () => throw new Exception('WorldScar is immutable. Consequences cannot be undone.'));
        static::deleting(fn () => throw new Exception('WorldScar is immutable. Consequences cannot be erased.'));
    }
}
