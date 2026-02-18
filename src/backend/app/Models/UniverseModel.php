<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UniverseModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'universes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'world_id',
        'name',
        'age',
        'state_vector',
        'parameters',
        'is_archived',
        'death_cause',
        'saga',
        'coords',
        'cosmic_faction_id',
    ];

    protected $casts = [
        'state_vector' => 'array',
        'parameters' => 'array',
        'age' => 'integer',
        'is_archived' => 'boolean',
        'coords' => 'array',
    ];

    public function world()
    {
        return $this->belongsTo(World::class, 'world_id');
    }

    public function cosmicFaction()
    {
        return $this->belongsTo(CosmicFaction::class, 'cosmic_faction_id');
    }

    public function fleets()
    {
        return $this->hasMany(Fleet::class, 'universe_id');
    }
}
