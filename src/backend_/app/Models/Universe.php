<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Universe extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'status',
        'world_blueprint_id',
        'multiverse_id',
        'parent_universe_id',
        'generation_id',
        'current_tick',
        'current_seed',
        'entropy',
        'stability_index',
        'existence_weight',
        'state_vector',
        'lifespan',
        'fitness_total_score',
        'fitness_stability_score',
        'fitness_complexity_score',
        'seed_dna',
    ];

    protected $casts = [
        'state_vector' => 'array',
        'seed_dna' => 'array',
        'entropy' => 'float',
        'stability_index' => 'float',
        'existence_weight' => 'float',
        'fitness_total_score' => 'float',
        'fitness_stability_score' => 'float',
        'fitness_complexity_score' => 'float',
        'current_tick' => 'integer',
        'current_seed' => 'integer',
        'lifespan' => 'integer',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Evolution\Generation::class, 'generation_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_universe_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_universe_id');
    }
}
