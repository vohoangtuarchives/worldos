<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HybridPreset extends Model
{
    use HasUuids;

    protected $table = 'hybrid_presets';

    protected $fillable = [
        'parent_preset_a',
        'parent_preset_b',
        'hybrid_type',
        'hybrid_equations',
        'collapse_conditions',
        'interaction_strength',
        'creation_tick',
        'identity_data',
        'is_active',
    ];

    protected $casts = [
        'hybrid_equations' => 'array',
        'collapse_conditions' => 'array',
        'interaction_strength' => 'float',
        'identity_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function getHybridName(): string
    {
        return $this->identity_data['name'] ?? 'Unknown Hybrid';
    }

    public function getHybridDescription(): string
    {
        return $this->identity_data['description'] ?? 'A hybrid preset created from world interaction.';
    }

    public function getCharacteristics(): array
    {
        return $this->identity_data['characteristics'] ?? [];
    }

    public function getDominantTraits(): array
    {
        return $this->identity_data['dominant_traits'] ?? [];
    }

    public function getEquation(string $type): ?string
    {
        return $this->hybrid_equations[$type] ?? null;
    }

    public function getCollapseCondition(string $type = 'primary'): ?string
    {
        if ($type === 'primary') {
            return $this->collapse_conditions['primary_condition'] ?? null;
        }

        return $this->collapse_conditions['secondary_conditions'][$type] ?? null;
    }

    public function isStableHybrid(): bool
    {
        return $this->hybrid_type === 'HYBRID_STABLE';
    }

    public function isChaoticHybrid(): bool
    {
        return $this->hybrid_type === 'HYBRID_CHAOTIC';
    }

    public function isComplexHybrid(): bool
    {
        return $this->hybrid_type === 'HYBRID_COMPLEX';
    }

    public function getCompatibilityScore(): float
    {
        // Calculate how compatible the parent presets were
        $baseCompatibility = $this->interaction_strength;
        
        // Adjust based on hybrid stability
        $stabilityBonus = $this->isStableHybrid() ? 0.2 : 0;
        $chaosPenalty = $this->isChaoticHybrid() ? -0.1 : 0;
        
        return max(0, min(1, $baseCompatibility + $stabilityBonus + $chaosPenalty));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByParents($query, string $parentA, string $parentB)
    {
        return $query->where(function ($q) use ($parentA, $parentB) {
            $q->where('parent_preset_a', $parentA)
              ->where('parent_preset_b', $parentB);
        })->orWhere(function ($q) use ($parentA, $parentB) {
            $q->where('parent_preset_a', $parentB)
              ->where('parent_preset_b', $parentA);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('hybrid_type', $type);
    }

    public function scopeStrong($query, float $minStrength = 0.7)
    {
        return $query->where('interaction_strength', '>=', $minStrength);
    }
}
