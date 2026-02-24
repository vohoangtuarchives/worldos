<?php

namespace WorldOS\Legacy\Application\World\Memory;

use Illuminate\Database\Eloquent\Model;

class ContradictionMemory extends Model
{
    protected $table = 'world_contradiction_memories';
    
    protected $fillable = [
        'world_id',
        'contradiction_id',
        'strategy_used',
        'effectiveness',
        'context_hash'
    ];

    protected $casts = [
        'effectiveness' => 'float'
    ];
}
