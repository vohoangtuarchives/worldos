<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class World extends Model
{
    // use HasUuids; // Table uses BigInt increments

    protected $table = 'worlds';

    protected $fillable = [
        'preset_version_id', 'name', 'slug', 'description'
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PresetVersion::class, 'preset_version_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(WorldMaterialOverride::class, 'world_id');
    }
}
