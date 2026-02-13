<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosmicEvent extends Model
{
    protected $table = 'cosmic_events';

    protected $fillable = [
        'world_id',
        'year',
        'type',
        'from_attractor',
        'to_attractor',
        'force',
        'metadata',
    ];

    protected $casts = [
        'force' => 'float',
        'metadata' => 'array',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
