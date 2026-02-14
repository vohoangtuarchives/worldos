<?php

namespace App\Domains\World;

use Illuminate\Database\Eloquent\Model;

class Scar extends Model
{
    protected $table = 'world_scars';
    
    protected $fillable = [
        'world_id',
        'location_scope',
        'constraint_rule',
        'severity',
        'origin_event_id'
    ];

    protected $casts = [
        'severity' => 'float'
    ];
}
