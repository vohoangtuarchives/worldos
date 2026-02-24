<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldMythModel extends Model
{
    protected $table = 'world_myths';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'theme',
        'description',
        'strength',
        'state',
        'tick_emerged',
        'belief_sources',
    ];

    protected function casts(): array
    {
        return [
            'strength' => 'float',
            'tick_emerged' => 'integer',
            'belief_sources' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
