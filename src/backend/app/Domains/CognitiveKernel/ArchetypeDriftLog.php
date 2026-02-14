<?php

namespace App\Domains\CognitiveKernel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\World;

class ArchetypeDriftLog extends Model
{
    use HasUuids;

    protected $table = 'archetype_drift_log';

    protected $fillable = [
        'world_id',
        'archetype_key',
        'drift_delta',
        'drift_sources',
        'tick',
        'context'
    ];

    protected $casts = [
        'drift_delta' => 'float',
        'drift_sources' => 'array',
        'context' => 'array',
        'tick' => 'integer',
    ];

    /**
     * Get the world this drift log belongs to
     */
    public function world()
    {
        return $this->belongsTo(World::class);
    }

    /**
     * Get the archetype definition
     */
    public function archetype(): ?Archetype
    {
        return Archetype::findByKey($this->archetype_key);
    }
}
