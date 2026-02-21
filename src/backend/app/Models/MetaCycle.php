<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaCycle extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'world_id',
        'current_generation',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'current_generation' => 'integer',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class, 'world_id');
    }
}
