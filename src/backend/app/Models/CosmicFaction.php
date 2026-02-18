<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CosmicFaction extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_SUPPRESSED = 'SUPPRESSED';
    public const STATUS_EXILED = 'EXILED';
    public const STATUS_DISSOLVED = 'DISSOLVED';
    public const STATUS_ERADICATED = 'ERADICATED';

    protected $fillable = [
        'name', 'ideology', 'color', 'stats',
        'status', 'parent_faction_id', 'cycle_origin', 'cycles_survived',
        'ideology_adaptability', 'resource_control', 'network_resilience',
    ];

    protected $casts = [
        'stats' => 'array',
        'ideology_adaptability' => 'float',
        'resource_control' => 'float',
        'network_resilience' => 'float',
    ];

    public function universes()
    {
        return $this->hasMany(UniverseModel::class, 'cosmic_faction_id');
    }

    public function parentFaction()
    {
        return $this->belongsTo(CosmicFaction::class, 'parent_faction_id');
    }

    public function childFactions()
    {
        return $this->hasMany(CosmicFaction::class, 'parent_faction_id');
    }
}
