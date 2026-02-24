<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Character extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'world_id',
        'name',
        'base_personality',
    ];

    protected $casts = [
        'base_personality' => 'array',
    ];

    public function memories(): HasMany
    {
        return $this->hasMany(CharacterMemory::class);
    }

    public function emotions(): HasMany
    {
        return $this->hasMany(CharacterEmotion::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(CharacterGoal::class);
    }
}
