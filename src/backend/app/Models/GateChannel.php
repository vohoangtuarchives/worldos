<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GateChannel extends Model
{
    protected $fillable = [
        'source_world_id',
        'target_world_id',
        'type',
        'stability',
        'throughput',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stability' => 'float',
        'throughput' => 'float',
    ];

    public function sourceWorld()
    {
        return $this->belongsTo(World::class, 'source_world_id');
    }

    public function targetWorld()
    {
        return $this->belongsTo(World::class, 'target_world_id');
    }
}
