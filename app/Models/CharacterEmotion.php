<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterEmotion extends Model
{
    protected $fillable = [
        'character_id',
        'type',
        'intensity',
        'decay_rate',
    ];
}
