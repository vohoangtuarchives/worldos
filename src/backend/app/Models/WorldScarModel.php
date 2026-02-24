<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldScarModel extends Model
{
    protected $table = 'world_scars';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'source_event',
        'type',
        'weight',
        'description',
        'tick_created',
        'current_intensity',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'tick_created' => 'integer',
            'current_intensity' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
