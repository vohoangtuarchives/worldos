<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ScarCounterforce extends Model
{
    use HasUuids;

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
