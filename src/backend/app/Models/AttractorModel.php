<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttractorModel extends Model
{
    protected $table = 'attractors';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'type',
        'magnitude',
        'basin_depth',
        'activation_threshold',
        'status',
        'current_pull',
    ];

    protected function casts(): array
    {
        return [
            'magnitude' => 'float',
            'basin_depth' => 'float',
            'activation_threshold' => 'float',
            'current_pull' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
