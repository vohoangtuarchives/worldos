<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ConflictPattern extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'escalation_curve' => 'array',
        'resolution_modes' => 'array',
    ];
}
