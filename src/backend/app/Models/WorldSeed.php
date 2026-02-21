<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldSeed extends Model
{
    protected $fillable = [
        'world_id',
        'seed_template_id',
        'state',
        'activation_tick',
        'exhaustion_tick',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'state' => \Tuzy\Domain\World\Enums\SeedState::class,
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SeedTemplate::class, 'seed_template_id');
    }

    /**
     * Force seed to DORMANT (delay activation)
     */
    public function forceDormant(): void
    {
        if ($this->state === \Tuzy\Domain\World\Enums\SeedState::EXHAUSTED) {
            throw new \Exception('Cannot reactivate EXHAUSTED seed');
        }

        $this->update([
            'state' => \Tuzy\Domain\World\Enums\SeedState::DORMANT,
            'activation_tick' => null,
        ]);
    }

    /**
     * Force seed to EXHAUSTED
     */
    public function forceExhaust(): void
    {
        $this->update([
            'state' => \Tuzy\Domain\World\Enums\SeedState::EXHAUSTED,
            'exhaustion_tick' => $this->world->clock->current_tick ?? 0,
        ]);
    }
}
