<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldSnapshot extends Model
{
    protected $fillable = [
        'world_id',
        'tick',
        'name',
        'description',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
