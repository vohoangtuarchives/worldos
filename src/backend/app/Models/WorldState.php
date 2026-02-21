<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorldState extends Model
{
    protected $fillable = [
        'power_axis',
        'resource_axis', 
        'perception_axis',
        'author_intent',
        'structural_anchor',
        'world_id',
        'resistance_factor',
        'state_vector',
        'evolution_profile_id',
        'current_phase',
    ];

    protected $casts = [
        'power_axis' => 'json',
        'resource_axis' => 'json',
        'perception_axis' => 'json',
        'author_intent' => 'json',
        'resistance_factor' => 'decimal:2',
        'state_vector' => 'json',
    ];

    public function materialSeeds(): HasMany
    {
        return $this->hasMany(MaterialSeed::class);
    }

    public function storyArcs(): HasMany
    {
        return $this->hasMany(StoryArc::class);
    }

    public function evolutionProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Tuzy\Domain\Evolution\Models\EvolutionProfile::class);
    }

    /**
     * Apply author intent to world state
     */
    public function applyIntent(array $intent): void
    {
        $this->author_intent = array_merge($this->author_intent ?? [], $intent);
        $this->save();
    }

    /**
     * Evolve axes based on time and intent
     */
    public function evolveAxes(): void
    {
        // Power axis evolution
        $this->power_axis = $this->evolveAxis($this->power_axis ?? [], 'power');
        
        // Resource axis evolution  
        $this->resource_axis = $this->evolveAxis($this->resource_axis ?? [], 'resource');
        
        // Perception axis evolution
        $this->perception_axis = $this->evolveAxis($this->perception_axis ?? [], 'perception');
        
        $this->save();
    }

    private function evolveAxis(array $axis, string $type): array
    {
        $evolutionRate = $this->author_intent[$type . '_gradient'] ?? 'medium';
        $rate = match($evolutionRate) {
            'steep' => 0.15,
            'medium' => 0.08, 
            'gentle' => 0.03,
            default => 0.05
        };

        // Apply evolution with resistance factor
        $effectiveRate = $rate * (1 - $this->resistance_factor);
        
        foreach ($axis as $key => $value) {
            $axis[$key] = min(1.0, $value + $effectiveRate);
        }

        return $axis;
    }

    /**
     * Calculate pressure points where axes collide
     */
    public function calculatePressurePoints(): array
    {
        $pressurePoints = [];
        
        $axes = [
            'power' => $this->power_axis ?? [],
            'resource' => $this->resource_axis ?? [],
            'perception' => $this->perception_axis ?? []
        ];

        // Find high-tension intersections
        foreach ($axes as $axis1Name => $axis1) {
            foreach ($axes as $axis2Name => $axis2) {
                if ($axis1Name === $axis2Name) continue;
                
                foreach ($axis1 as $element => $value1) {
                    $value2 = $axis2[$element] ?? 0;
                    
                    // High tension when both axes have high values
                    if ($value1 > 0.7 && $value2 > 0.7) {
                        $pressurePoints[] = [
                            'axes' => "{$axis1Name}_{$axis2Name}",
                            'element' => $element,
                            'tension' => ($value1 + $value2) / 2,
                            'type' => 'collision'
                        ];
                    }
                }
            }
        }

        return $pressurePoints;
    }
}
