<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scene extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'timeline_node_id',
        'goal',
        'tension_target',
        'status',
        'state',
    ];

    protected $casts = [
        'state' => 'array',
        'tension_target' => 'float',
    ];

    public function timelineNode(): BelongsTo
    {
        return $this->belongsTo(TimelineNode::class);
    }
}
