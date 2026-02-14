<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CharacterArchetype extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'desire_vector' => 'array',
        'fear_vector' => 'array',
        'contradiction_index' => 'float',
    ];
}
