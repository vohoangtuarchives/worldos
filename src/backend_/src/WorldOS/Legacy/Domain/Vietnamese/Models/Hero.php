<?php

namespace WorldOS\Legacy\Domain\Vietnamese\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Hero extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'world_heroes';

    protected $fillable = [
        'world_id',
        'name',
        'other_names',
        'archetype',
        'dimensions',
        'impact_score',
        'biography',
        'era',
        'origin_hero_id',
        'is_generated',
        'status',
        'spawned_at_tick'
    ];

    protected $casts = [
        'dimensions' => 'array',
        'other_names' => 'array',
        'is_generated' => 'boolean',
        'spawned_at_tick' => 'integer',
        'impact_score' => 'float',
    ];

    public function world()
    {
        return $this->belongsTo(\App\Models\World::class);
    }
}
