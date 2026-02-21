<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Universe extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'world_id',
        'generation',
        'fitness',
        'state',
        'year',
        'parameters',
    ];

    protected $casts = [
        'state' => 'array',
        'parameters' => 'array',
        'fitness' => 'float',
        'generation' => 'integer',
        'year' => 'integer',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class, 'world_id');
    }
}
