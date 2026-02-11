<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MythSymbol extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'symbolic_axis' => 'array',
        'inversion_potential' => 'float',
    ];
}
