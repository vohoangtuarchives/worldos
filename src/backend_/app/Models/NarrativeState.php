<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Structural fingerprint per series: arc_progress, last_beat, tension, optional world snapshot.
 */
class NarrativeState extends Model
{
    protected $table = 'narrative_state';

    protected $fillable = [
        'narrative_series_id',
        'arc_progress',
        'last_emotional_beat',
        'last_tension',
        'foreshadow_cooldown',
        'world_snapshot',
        'narrative_driven_state',
    ];

    protected $casts = [
        'arc_progress' => 'float',
        'last_tension' => 'float',
        'foreshadow_cooldown' => 'integer',
        'world_snapshot' => 'array',
        'narrative_driven_state' => 'array',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeries::class, 'narrative_series_id');
    }
}
