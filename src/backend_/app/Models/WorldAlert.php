<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldAlert extends Model
{
    protected $fillable = ['world_id', 'type', 'severity', 'message', 'details', 'resolved', 'resolved_at', 'resolved_by', 'resolution_notes'];

    protected $casts = [
        'details' => 'array',
        'resolved' => 'boolean',
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
