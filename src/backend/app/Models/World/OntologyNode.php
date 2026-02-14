<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OntologyNode extends Model
{
    use HasUuids;

    protected $table = 'ontology_nodes';

    protected $fillable = [
        'preset_version_id', 'parent_id', 'slug', 'name', 'path', 'depth'
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PresetVersion::class, 'preset_version_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OntologyNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OntologyNode::class, 'parent_id');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(
            PresetMaterial::class,
            'preset_material_ontology',
            'ontology_node_id',
            'preset_material_id'
        );
    }
}
