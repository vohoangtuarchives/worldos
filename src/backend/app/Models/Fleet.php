<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Fleet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'universe_id',
        'faction_id',
        'commander_id',
        'name',
        'type',
        'strength',
        'coordinates',
        'status',
        'destination_universe_id'
    ];

    protected $casts = [
        'strength' => 'float',
        'coordinates' => 'array',
    ];

    public function universe()
    {
        return $this->belongsTo(\App\Domains\Cosmology\Entities\Universe::class, 'universe_id'); // Note: Universe is usually an Entity, but mapped to DB via Model. Make sure to use the correct Eloquent model if separated.
    }

    public function faction()
    {
        return $this->belongsTo(\App\Models\CosmicFaction::class);
    }
}
