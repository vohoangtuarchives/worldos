<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldInteraction extends Model
{
    use HasUuids;

    protected $table = 'world_interactions';

    protected $fillable = [
        'world_a_id',
        'world_b_id',
        'interaction_type',
        'strength',
        'active_from_tick',
        'active_to_tick',
        'metadata',
    ];

    protected $casts = [
        'strength' => 'float',
        'metadata' => 'array',
    ];

    public function worldA(): BelongsTo
    {
        return $this->belongsTo(WorldState::class, 'world_a_id');
    }

    public function worldB(): BelongsTo
    {
        return $this->belongsTo(WorldState::class, 'world_b_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('active_to_tick');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('interaction_type', $type);
    }

    public function scopeStrong($query, float $minStrength = 0.5)
    {
        return $query->where('strength', '>=', $minStrength);
    }

    public function getDuration(): ?int
    {
        if ($this->active_to_tick === null) {
            return null;
        }

        return $this->active_to_tick - $this->active_from_tick;
    }

    public function isActive(): bool
    {
        return $this->active_to_tick === null;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    public function setMetadataValue(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;
    }
}
