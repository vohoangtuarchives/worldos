<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaLayerState extends Model
{
    // Singleton-like table, usually just ID=1
    protected $table = 'meta_layer_states';

    protected $fillable = [
        'chaos_pool',
        'entropy_pressure',
        'resource_flux',
        'ideology_vector',
        'myth_field',
        'aggression_index',
        'stability_index',
        'mutation_bias',
        'current_era_index',
        'last_evolved_at',
    ];

    protected $casts = [
        'chaos_pool' => 'float',
        'entropy_pressure' => 'float',
        'resource_flux' => 'float',
        'ideology_vector' => 'array',
        'myth_field' => 'array',
        'aggression_index' => 'float',
        'stability_index' => 'float',
        'mutation_bias' => 'float',
        'current_era_index' => 'integer',
        'last_evolved_at' => 'datetime',
    ];

    /**
     * Get or create the Singleton instance
     */
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'ideology_vector' => [
                'order' => 0.5,
                'chaos' => 0.5,
                'expansion' => 0.5,
                'consolidation' => 0.5,
                'diversity' => 0.5,
            ],
            'chaos_pool' => 0,
            'entropy_pressure' => 0,
        ]);
    }
}
