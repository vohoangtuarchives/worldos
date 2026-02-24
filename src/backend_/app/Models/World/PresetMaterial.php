<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PresetMaterial extends Model
{
    use HasUuids;

    protected $table = 'preset_materials';

    protected $fillable = [
        'preset_version_id', 'type', 'slug', 'name',
        'metadata', 'power_scale', 'rarity'
    ];

    protected $casts = [
        'metadata' => 'array',
        'power_scale' => 'float',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PresetVersion::class, 'preset_version_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            OntologyNode::class,
            'preset_material_ontology',
            'preset_material_id',
            'ontology_node_id'
        );
    }
}
