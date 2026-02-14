<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorySeed extends Model
{
    protected $fillable = [
        'story_id',
        'type',
        'dimension',
        'severity',
        'age',
        'status',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
