<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldStateMetric extends Model
{
    protected $table = 'world_state_metrics';
    protected $primaryKey = 'world_state_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // migration has computed_at

    protected $fillable = [
        'world_state_id',
        'strongest_character_id',
        'total_power',
        'active_conflict_count',
        'myth_density',
        'computed_at',
    ];

    protected $casts = [
        'total_power' => 'decimal:2',
        'myth_density' => 'decimal:4',
        'computed_at' => 'datetime',
    ];

    public function worldState(): BelongsTo
    {
        return $this->belongsTo(WorldState::class, 'world_state_id');
    }
}
