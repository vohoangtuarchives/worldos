<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDraft extends Model
{
    use HasUuids;

    protected $table = 'material_drafts';

    protected $fillable = [
        'preset_version_id', 'payload', 'proposed_ontology_nodes', 'status'
    ];

    protected $casts = [
        'payload' => 'array',
        'proposed_ontology_nodes' => 'array',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PresetVersion::class, 'preset_version_id');
    }
}
