<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresetVersion extends Model
{
    use HasUuids;

    protected $table = 'preset_versions';

    protected $fillable = [
        'preset_id', 'version_label', 'parent_version_id', 'status',
        'power_policy', 'resource_policy', 'conflict_policy',
        'escalation_policy', 'myth_policy', 'scar_policy',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function preset(): BelongsTo
    {
        return $this->belongsTo(WorldPreset::class, 'preset_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(PresetMaterial::class, 'preset_version_id');
    }

    public function ontologyNodes(): HasMany
    {
        return $this->hasMany(OntologyNode::class, 'preset_version_id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(MaterialDraft::class, 'preset_version_id');
    }
}
