<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealingEvent extends Model
{

    protected $guarded = [];

    protected $casts = [
        'methodology_vector' => 'array',
        'effectiveness_score' => 'float',
        'tick' => 'integer',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function scar()
    {
        return $this->belongsTo(Scar::class, 'target_scar_id');
    }
}
