<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldPowerProfile extends Model
{


    protected $guarded = [];

    protected $casts = [
        'parameters' => 'array',
        'material_affinities' => 'array',
        'progression_state' => 'array',
        'collision_traits' => 'array',
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}