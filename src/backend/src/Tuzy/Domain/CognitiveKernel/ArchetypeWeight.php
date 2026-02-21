<?php

namespace Tuzy\Domain\CognitiveKernel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\World;

class ArchetypeWeight extends Model
{
    use HasUuids;

    protected $fillable = [
        'world_id',
        'archetype_key',
        'weight',
        'effective_weight',
        'drift_history'
    ];

    protected $casts = [
        'weight' => 'float',
        'effective_weight' => 'float',
        'drift_history' => 'array',
    ];

    /**
     * Get the world this weight belongs to
     */
    public function world()
    {
        return $this->belongsTo(World::class);
    }

    /**
     * Get the archetype definition
     */
    public function archetype(): ?Archetype
    {
        return Archetype::findByKey($this->archetype_key);
    }

    /**
     * Calculate effective weight (baseline + drift + legacy influence - trauma)
     */
    public function calculateEffectiveWeight(float $legacyInfluence = 0, float $trauma = 0): float
    {
        $archetype = $this->archetype();
        if (!$archetype) {
            return $this->weight;
        }

        $effective = $this->weight + $legacyInfluence - $trauma;
        
        return max(0, min(1, $effective));
    }

    /**
     * Record drift change
     */
    public function recordDrift(float $delta, array $sources): void
    {
        $history = $this->drift_history ?? [];
        $history[] = [
            'timestamp' => now()->toIso8601String(),
            'delta' => $delta,
            'sources' => $sources,
            'weight_before' => $this->weight,
            'weight_after' => $this->weight + $delta
        ];

        $this->drift_history = $history;
        $this->weight = max(0, min(1, $this->weight + $delta));
        $this->save();
    }
}
