<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerFaction extends Model
{
    protected $table = 'player_faction';

    protected $fillable = ['user_id', 'universe_id', 'faction_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function universe(): BelongsTo
    {
        return $this->belongsTo(UniverseModel::class, 'universe_id', 'id');
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(CosmicFaction::class, 'faction_id');
    }
}
