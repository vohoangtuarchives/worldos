<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GenesisSeed extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'metaphysics_vector' => 'array',
        'tags' => 'array',
        'instability_index' => 'float',
    ];
}
