<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniverseStyleModel extends Model
{
    protected $table = 'universe_styles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'genre',
        'style_vector',
        'name',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'style_vector' => 'array',
            'version' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
