<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Eloquent Model for worlds table.
 *
 * Infrastructure layer — NO business logic.
 * Domain logic belongs in WorldEntity.
 *
 * @property string $id
 * @property string $name
 * @property array $law_vector
 * @property string $preset_key
 * @property string|null $origin_type
 * @property string $status
 * @property array|null $config
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class World extends Model
{
    use HasUuids;

    protected $table = 'worlds';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'law_vector',
        'preset_key',
        'origin_type',
        'status',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'law_vector' => 'array',
            'config' => 'array',
        ];
    }
}
