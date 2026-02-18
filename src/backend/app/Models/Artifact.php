<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Artifact extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'origin_universe_id',
        'owner_faction_id',
        'power_stats',
        'rarity',
        'status',
    ];

    protected $casts = [
        'power_stats' => 'array',
    ];

    public function originUniverse()
    {
        return $this->belongsTo(UniverseModel::class, 'origin_universe_id');
    }
}
