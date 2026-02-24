<?php

namespace WorldOS\Saga\Domain\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SagaObservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'saga_id',
        'observation_type',
        'observation',
        'context',
        'confidence'
    ];

    protected $casts = [
        'context' => 'array',
        'confidence' => 'float',
    ];

    /**
     * Observation types
     */
    public const TYPE_PATTERN = 'pattern';
    public const TYPE_DIVERGENCE = 'divergence';
    public const TYPE_ARCHETYPE_SHIFT = 'archetype_shift';

    /**
     * Get the saga
     */
    public function saga()
    {
        return $this->belongsTo(Saga::class);
    }
}
