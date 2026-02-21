<?php

namespace Tuzy\Domain\World;

use Illuminate\Database\Eloquent\Model;

class Myth extends Model
{
    protected $table = 'world_myths';
    
    protected $fillable = [
        'world_id',
        'truth_statement',
        'rigidity',
        'origin_event_id'
    ];

    protected $casts = [
        'rigidity' => 'float'
    ];
}
