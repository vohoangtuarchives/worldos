<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'story_id',
        'order',
        'title',
        'content',
        'resolved_seed_id',
        'generated_seeds',
    ];

    protected $casts = [
        'generated_seeds' => 'array',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
