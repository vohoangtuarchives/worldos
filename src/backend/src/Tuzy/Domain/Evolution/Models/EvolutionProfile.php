<?php

namespace Tuzy\Domain\Evolution\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EvolutionProfile extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'coefficients' => 'array',
        'thresholds' => 'array',
        'is_active' => 'boolean',
        'alpha' => 'float',
    ];

    /**
     * Get the default profile for a new world.
     */
    public static function default(): self
    {
        return self::firstOrCreate([
            'name' => 'Balance Standard',
        ], [
            'coefficients' => [
                'belief_growth' => 0.05,
                'entropy_decay' => -0.02,
                'resource_consumption' => 0.1,
                'innovation_bonus' => 0.02,
                'stability_recovery' => 0.05,
            ],
            'thresholds' => [
                'stability_critical' => 0.3,
                'entropy_collapse' => 0.9,
            ],
            'alpha' => 1.0,
        ]);
    }
}
