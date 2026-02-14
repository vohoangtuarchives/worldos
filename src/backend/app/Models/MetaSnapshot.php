<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulation_run_id',
        'tick',
        'current_era_index',
        'extinction_threshold',
        'drift_velocity',
        'ideology_vector',
        'sacred_state',
        'meta_hash',
    ];

    protected $casts = [
        'tick' => 'integer',
        'current_era_index' => 'integer',
        'extinction_threshold' => 'float',
        'drift_velocity' => 'float',
        'ideology_vector' => 'array',
        'sacred_state' => 'array',
    ];
}
