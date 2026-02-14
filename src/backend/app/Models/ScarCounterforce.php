<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScarCounterforce extends Model
{

    protected $guarded = [];

    protected $casts = [
        'healing_vector' => 'array',
        'created_tick' => 'integer',
        'strength' => 'float',
    ];

    public function scar()
    {
        return $this->belongsTo(Scar::class);
    }
}
