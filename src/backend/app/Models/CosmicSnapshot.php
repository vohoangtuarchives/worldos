<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosmicSnapshot extends Model
{
    protected $table = 'cosmic_snapshots';

    protected $fillable = [
        'world_id',
        'year',
        'energy',
        'entropy',
        'tension',
        'stability',
        'resonance',
        'information_density',
        'transcendence',
        'attractor',
        'attractor_incarnation_id',
        'env_ley_energy',
        'env_terrain_stability',
        'env_biosphere_vitality',
        'env_anomaly_density',
        'civ_knowledge',
        'civ_ritual_coherence',
        'civ_tech_level',
        'civ_faction_stability',
        'civ_resonance_accumulator',
        'civ_resilience',
        'social_classes',
        'composite_tension',
    ];

    protected $casts = [
        'energy' => 'float',
        'entropy' => 'float',
        'tension' => 'float',
        'stability' => 'float',
        'resonance' => 'float',
        'information_density' => 'float',
        'transcendence' => 'float',
        'env_ley_energy' => 'float',
        'env_terrain_stability' => 'float',
        'env_biosphere_vitality' => 'float',
        'env_anomaly_density' => 'float',
        'civ_knowledge' => 'float',
        'civ_ritual_coherence' => 'float',
        'civ_tech_level' => 'float',
        'civ_faction_stability' => 'float',
        'civ_resonance_accumulator' => 'float',
        'civ_resilience' => 'float',
        'social_classes' => 'array',
        'composite_tension' => 'float',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
