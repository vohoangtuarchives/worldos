<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorldPreset extends Model
{
    use HasUuids;

    protected $table = 'world_presets';

    protected $fillable = [
        'code',
        'name',
        'power_policy',
        'resource_policy',
        'conflict_policy',
        'escalation_policy',
        'myth_policy',
        'scar_policy',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];
}
