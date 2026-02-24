<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorldMyth extends Model
{
    protected $fillable = [
        'world_id',
        'name',
        'description',
        'status',
        'strength',
        'origin_universe_id',
        'genre_origin',
        'affected_materials',
        'canonized_at',
    ];

    protected $casts = [
        'affected_materials' => 'array',
        'canonized_at' => 'datetime',
        'strength' => 'float',
    ];

    public function beliefs(): BelongsToMany
    {
        return $this->belongsToMany(WorldBelief::class, 'belief_myth', 'myth_id', 'belief_id');
    }

    protected static function booted()
    {
        static::updating(function ($myth) {
            if ($myth->isDirty('status') && $myth->status === 'merged') {
                // Merged myths lose their individual strength
                $myth->strength = 0;
            }
        });
    }
}
