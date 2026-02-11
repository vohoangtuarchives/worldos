<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReaderSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'world_id',
        'last_active_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_active_at' => 'datetime',
    ];

    public function reactions(): HasMany
    {
        return $this->hasMany(ReaderReaction::class, 'session_id');
    }
}
