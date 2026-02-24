<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StyleProposal extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'world_id',
        'proposed_adjustments',
        'reasoning',
        'predicted_improvement',
        'status',
    ];

    protected $casts = [
        'proposed_adjustments' => 'array',
        'predicted_improvement' => 'float',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
