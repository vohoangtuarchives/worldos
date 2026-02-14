<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Theme extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'philosophical_vector' => 'array',
        'moral_axis' => 'array',
        'emotional_axis' => 'array',
        'compatible_conflicts' => 'array',
    ];
}
