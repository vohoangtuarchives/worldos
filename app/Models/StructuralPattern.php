<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StructuralPattern extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'arc_template' => 'array',
    ];
}
