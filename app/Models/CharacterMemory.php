<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CharacterMemory extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'character_id',
        'type',
        'content',
        'visibility',
        'confidence',
        'embedding',
        'timeline_node_id',
    ];
}
