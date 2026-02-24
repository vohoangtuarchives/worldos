<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WorldOS V5 Universe Eloquent Model.
 * Clean V5 schema — no V3 legacy fields.
 */
class UniverseModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'universes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'status',
        // Lineage (DAG)
        'world_blueprint_id',
        'multiverse_id',
        'parent_universe_id',
        // Simulation state
        'generation_id',
        'current_tick',
        'current_seed',
        'entropy',
        'stability_index',
        'existence_weight',
        'state_vector',
        'seed_dna',
        'fitness_total_score',
        'lifespan',
        // V6 Ontology
        'culture_vector',
        'ideology_vector',
        'influence_mass',
        'stability_duration',
        'lifecycle_state',
    ];

    protected $casts = [
        'state_vector'     => 'array',
        'current_tick'     => 'integer',
        'current_seed'     => 'integer',
        'entropy'          => 'float',
        'stability_index'  => 'float',
        'existence_weight' => 'float',
        'culture_vector'   => 'array',
        'ideology_vector'  => 'array',
        'influence_mass'   => 'float',
        'stability_duration' => 'integer',
    ];

    public function snapshots()
    {
        return $this->hasMany(UniverseSnapshot::class, 'universe_id');
    }

    public function parentUniverse()
    {
        return $this->belongsTo(self::class, 'parent_universe_id');
    }

    public function childUniverses()
    {
        return $this->hasMany(self::class, 'parent_universe_id');
    }
}
