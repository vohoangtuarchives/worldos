<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorldState extends Model
{
    use HasUuids;

    protected $table = 'world_states';

    protected $fillable = [
        'preset_id',
        'preset_version_id',
        'version',
        'seed',
        'snapshot',
        'parent_state_id',
        'created_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'created_at' => 'datetime', // Already default, but good to be explicit for immutable nature
    ];

    public $timestamps = false; // Only created_at exists in migration? No, `timestamps()` adds both. Let me check the migration again. Migration used $table->timestamp('created_at') explicitly. Ah wait.

    // Migration used $table->timestamp('created_at'); explicitly, not $table->timestamps();
    // So we need to disable updated_at.
    
    public function setUpdatedAt($value)
    {
        // Do nothing, immutable
    }
    
    public function getUpdatedAtColumn()
    {
        return null;
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(WorldPreset::class, 'preset_id');
    }

    public function presetVersion(): BelongsTo
    {
        return $this->belongsTo(PresetVersion::class, 'preset_version_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WorldState::class, 'parent_state_id');
    }

    public function metrics(): HasOne
    {
        return $this->hasOne(WorldStateMetric::class, 'world_state_id');
    }
}
