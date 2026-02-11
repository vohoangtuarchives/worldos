<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldMaterialOverride extends Model
{
    use HasUuids;

    protected $table = 'world_material_overrides';

    protected $fillable = [
        'world_id', 'preset_material_id', 'override_mode',
        'slug', 'name', 'metadata', 'power_scale_modifier', 'rarity_override'
    ];

    protected $casts = [
        'metadata' => 'array',
        'power_scale_modifier' => 'float',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class, 'world_id');
    }

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(PresetMaterial::class, 'preset_material_id');
    }
}
