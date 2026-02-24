<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PowerSystem extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'cost_model' => 'array',
        'corruption_factor' => 'float',
        'scaling_limit' => 'float',
    ];
}
