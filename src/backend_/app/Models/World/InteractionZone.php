<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InteractionZone extends Model
{
    use HasUuids;

    protected $table = 'interaction_zones';

    protected $fillable = [
        'zone_coherence',
        'dominant_narratives',
        'active_worlds',
        'active_interactions',
        'formation_tick',
        'collapse_tick',
        'zone_metrics',
    ];

    protected $casts = [
        'zone_coherence' => 'float',
        'dominant_narratives' => 'array',
        'active_worlds' => 'array',
        'active_interactions' => 'array',
        'zone_metrics' => 'array',
    ];

    public function getWorldCount(): int
    {
        return count($this->active_worlds ?? []);
    }

    public function getInteractionCount(): int
    {
        return count($this->active_interactions ?? []);
    }

    public function getDominantNarrative(): ?string
    {
        $narratives = $this->dominant_narratives ?? [];
        return $narratives[0] ?? null;
    }

    public function hasNarrative(string $narrative): bool
    {
        return in_array($narrative, $this->dominant_narratives ?? []);
    }

    public function getNarrativeCount(string $narrative): int
    {
        $worlds = $this->active_worlds ?? [];
        return count(array_filter($worlds, fn($w) => $w['preset'] === $narrative));
    }

    public function isStable(): bool
    {
        return $this->zone_coherence > 0.6;
    }

    public function isUnstable(): bool
    {
        return $this->zone_coherence < 0.3;
    }

    public function isCollapsed(): bool
    {
        return $this->collapse_tick !== null;
    }

    public function getDuration(): ?int
    {
        if ($this->collapse_tick === null) {
            return null;
        }

        return $this->collapse_tick - $this->formation_tick;
    }

    public function getZoneMetric(string $key, $default = null)
    {
        return data_get($this->zone_metrics, $key, $default);
    }

    public function setZoneMetric(string $key, $value): void
    {
        $metrics = $this->zone_metrics ?? [];
        data_set($metrics, $key, $value);
        $this->zone_metrics = $metrics;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('collapse_tick');
    }

    public function scopeCollapsed($query)
    {
        return $query->whereNotNull('collapse_tick');
    }

    public function scopeStable($query)
    {
        return $query->where('zone_coherence', '>', 0.6);
    }

    public function scopeUnstable($query)
    {
        return $query->where('zone_coherence', '<', 0.3);
    }

    public function scopeByNarrative($query, string $narrative)
    {
        return $query->whereJsonContains('dominant_narratives', $narrative);
    }

    public function scopeFormedAfter($query, int $tick)
    {
        return $query->where('formation_tick', '>=', $tick);
    }

    public function scopeFormedBefore($query, int $tick)
    {
        return $query->where('formation_tick', '<=', $tick);
    }
}
