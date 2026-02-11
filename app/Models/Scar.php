<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Scar extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'belief_shift_vector' => 'array',
        'created_tick' => 'integer',
        'resolved_tick' => 'integer',
        'pain_score' => 'float',
        'decay_rate' => 'float',
    ];

    public function counterforces()
    {
        return $this->hasMany(ScarCounterforce::class);
    }
}
