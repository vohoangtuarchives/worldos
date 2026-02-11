<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIWorldReport extends Model
{
    protected $table = 'ai_world_reports';

    protected $fillable = [
        'world_id',
        'type',
        'content',
        'suggestion',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
