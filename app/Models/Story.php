<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'world_id',
        'title',
        'status',
        'world_state',
        'character_state',
    ];

    protected $casts = [
        'world_state' => 'array',
        'character_state' => 'array',
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function seeds()
    {
        return $this->hasMany(StorySeed::class);
    }
}
