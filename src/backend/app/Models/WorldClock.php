<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldClock extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['world_id', 'current_tick'];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    /**
     * Increment tick atomically
     * This should only be called by WorldClockService
     */
    public function incrementTick(int $step = 1): int
    {
        $this->increment('current_tick', $step);
        $this->refresh();

        return $this->current_tick;
    }
}
