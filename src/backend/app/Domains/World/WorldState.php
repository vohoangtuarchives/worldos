<?php

namespace App\Domains\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorldState extends Model
{
    use HasUuids;

    protected $table = 'world_states';

    protected $fillable = [
        'id',
        'current_preset',
        'coherence',
        'entropy',
        'stability',
        'dominance_level',
        'permeability',
        'position_x',
        'position_y',
        'belief_mass',
        'data_consistency',
        'ritual_density',
        'contradiction_index',
        'propaganda_effort',
        'war_probability',
        'scarcity_rate',
        'resource_flow',
        'randomness',
        'rigidity',
        'adaptability',
        'narrative_resources',
        'cross_preset_knowledge',
        'active_conflicts',
        'emergent_properties',
        'pending_hybrid',
        'economic_focus',
        'governance_level',
        'resource_inequality',
        'social_unrest',
        'depletion_rate',
        'organization_level',
        'corruption_level',
        'stabilization_influence',
        'creative_destruction',
        'innovation_pressure'
    ];

    protected $casts = [
        'coherence' => 'float',
        'entropy' => 'float',
        'stability' => 'float',
        'dominance_level' => 'float',
        'permeability' => 'float',
        'position_x' => 'float',
        'position_y' => 'float',
        'belief_mass' => 'float',
        'data_consistency' => 'float',
        'ritual_density' => 'float',
        'contradiction_index' => 'float',
        'propaganda_effort' => 'float',
        'war_probability' => 'float',
        'scarcity_rate' => 'float',
        'resource_flow' => 'float',
        'randomness' => 'float',
        'rigidity' => 'float',
        'adaptability' => 'float',
        'narrative_resources' => 'float',
        'cross_preset_knowledge' => 'float',
        'active_conflicts' => 'array',
        'emergent_properties' => 'array',
        'pending_hybrid' => 'array',
        'economic_focus' => 'float',
        'governance_level' => 'float',
        'resource_inequality' => 'float',
        'social_unrest' => 'float',
        'depletion_rate' => 'float',
        'organization_level' => 'float',
        'corruption_level' => 'float',
        'stabilization_influence' => 'float',
        'creative_destruction' => 'float',
        'innovation_pressure' => 'float'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Initialize default values for new worlds
        $this->narrative_resources = $this->narrative_resources ?? 0.5;
        $this->cross_preset_knowledge = $this->cross_preset_knowledge ?? 0.0;
        $this->active_conflicts = $this->active_conflicts ?? [];
        $this->emergent_properties = $this->emergent_properties ?? [];
        $this->pending_hybrid = $this->pending_hybrid ?? null;
        $this->economic_focus = $this->economic_focus ?? 0.5;
        $this->governance_level = $this->governance_level ?? 0.5;
        $this->resource_inequality = $this->resource_inequality ?? 0.3;
        $this->social_unrest = $this->social_unrest ?? 0.2;
        $this->depletion_rate = $this->depletion_rate ?? 0.1;
        $this->organization_level = $this->organization_level ?? 0.5;
        $this->corruption_level = $this->corruption_level ?? 0.2;
        $this->stabilization_influence = $this->stabilization_influence ?? 0.0;
        $this->creative_destruction = $this->creative_destruction ?? 0.0;
        $this->innovation_pressure = $this->innovation_pressure ?? 0.3;
    }

    // Accessors for backward compatibility
    public function getIdAttribute(): string
    {
        return $this->attributes['id'] ?? $this->id;
    }

    public function getCurrentPresetAttribute(): string
    {
        return $this->attributes['current_preset'] ?? $this->currentPreset ?? 'stable';
    }

    public function getCoherenceAttribute(): float
    {
        return $this->attributes['coherence'] ?? $this->coherence ?? 0.5;
    }

    public function getEntropyAttribute(): float
    {
        return $this->attributes['entropy'] ?? $this->entropy ?? 0.3;
    }

    public function getStabilityAttribute(): float
    {
        return $this->attributes['stability'] ?? $this->stability ?? 0.5;
    }

    public function getDominanceLevelAttribute(): float
    {
        return $this->attributes['dominance_level'] ?? $this->dominanceLevel ?? 0.5;
    }

    public function getPermeabilityAttribute(): float
    {
        return $this->attributes['permeability'] ?? $this->permeability ?? 0.5;
    }

    public function evolve(): void
    {
        // Basic evolution logic - can be extended with preset-specific evolution
        $this->coherence += (rand(-5, 5) / 1000);
        $this->entropy += (rand(-2, 8) / 1000);
        $this->stability += (rand(-3, 3) / 1000);
        
        // Apply bounds
        $this->coherence = max(0, min(1, $this->coherence));
        $this->entropy = max(0, min(1, $this->entropy));
        $this->stability = max(0, min(1, $this->stability));
    }

    public function applyBeliefMutation(array $mutation): void
    {
        $type = $mutation['type'];
        $strength = $mutation['strength'];
        
        switch ($type) {
            case 'BELIEF_CONTAMINATION':
                $this->contradictionIndex += $strength * 0.1;
                $this->beliefMass += $strength * 0.05;
                break;
            case 'REALITY_DISTORTION':
                $this->entropy += $strength * 0.15;
                $this->coherence -= $strength * 0.1;
                break;
            case 'RESOURCE_CROSSFLOW':
                $this->resourceFlow += $strength * 0.1;
                $this->economicFocus += $strength * 0.05;
                break;
        }
    }

    public function applyResourceExchange(array $exchange): void
    {
        $type = $exchange['type'];
        $amount = $exchange['amount'] ?? 0;
        $efficiency = $exchange['efficiency'] ?? 1.0;
        
        switch ($type) {
            case 'RESOURCE_CROSSFLOW':
                $this->narrativeResources += $amount * $efficiency;
                $this->crossPresetKnowledge += $amount * 0.1;
                break;
        }
    }

    public function isCritical(): bool
    {
        return $this->entropy > 0.8 || $this->coherence < 0.2 || $this->stability < 0.3;
    }

    public function isStable(): bool
    {
        return $this->entropy < 0.4 && $this->coherence > 0.6 && $this->stability > 0.6;
    }

    public function getInteractionStrength(WorldState $other): float
    {
        $distance = sqrt(
            pow($this->position_x - $other->position_x, 2) + 
            pow($this->position_y - $other->position_y, 2)
        );
        
        return (
            ($this->dominance_level * $other->permeability) +
            ($other->dominance_level * $this->permeability)
        ) * max(0, 1 - $distance * 0.01);
    }

    public function canInteractWith(WorldState $other): bool
    {
        $distance = sqrt(
            pow($this->position_x - $other->position_x, 2) + 
            pow($this->position_y - $other->position_y, 2)
        );
        
        return $distance < 100; // Interaction range
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'is_critical' => $this->isCritical(),
            'is_stable' => $this->isStable(),
            'interaction_count' => count($this->active_conflicts ?? []),
            'emergent_property_count' => count($this->emergent_properties ?? [])
        ]);
    }
}
