<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorldEvent extends Model
{
    protected $fillable = [
        'world_id',
        'tick',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function scar(): HasOne
    {
        return $this->hasOne(WorldScar::class, 'source_event_id');
    }

    /**
     * Enforce Immutability: WorldEvent is a fact of history.
     * It cannot be changed or deleted.
     */
    protected static function booted(): void
    {
        static::updating(fn () => throw new Exception('WorldEvent is immutable. History cannot be changed.'));
        static::deleting(fn () => throw new Exception('WorldEvent is immutable. History cannot be erased.'));
    }
}
