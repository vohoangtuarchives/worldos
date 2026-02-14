<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorldSnapshotV2 extends Model
{
    use HasFactory;

    protected $table = 'world_snapshots_v2';

    protected $fillable = [
        'simulation_run_id',
        'world_id',
        'tick',
        'generation',
        'archetype_id',
        'status',
        'entropy',
        'survival_score',
        'is_prophet',
        'state_payload',
        'state_hash',
    ];

    protected $casts = [
        'tick' => 'integer',
        'generation' => 'integer',
        'entropy' => 'float',
        'survival_score' => 'float',
        'is_prophet' => 'boolean',
        'state_payload' => 'array',
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
