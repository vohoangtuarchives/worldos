<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorldBelief extends Model
{
    protected $fillable = [
        'world_id',
        'content',
        'intensity',
        'repeat_count',
    ];

    public function myths(): BelongsToMany
    {
        return $this->belongsToMany(WorldMyth::class, 'belief_myth', 'belief_id', 'myth_id');
    }
}
