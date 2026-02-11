<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterGoal extends Model
{
    protected $fillable = [
        'character_id',
        'description',
        'priority',
        'status',
    ];
}
