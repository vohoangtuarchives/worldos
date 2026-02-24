<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent Model for universes table.
 *
 * Infrastructure layer — NO business logic.
 * Domain logic belongs in UniverseEntity.
 *
 * @property string $id
 * @property string $world_id
 * @property array $state_vector
 * @property array|null $cascade_state
 * @property int $current_tick
 * @property int $age
 * @property string $status
 * @property int $random_seed
 * @property string|null $parent_universe_id
 * @property array|null $parameters
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UniverseModel extends Model
{
    use HasUuids;

    protected $table = 'universes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'world_id',
        'state_vector',
        'cascade_state',
        'current_tick',
        'age',
        'status',
        'random_seed',
        'parent_universe_id',
        'parameters',
    ];

    protected function casts(): array
    {
        return [
            'state_vector' => 'array',
            'cascade_state' => 'array',
            'current_tick' => 'integer',
            'age' => 'integer',
            'random_seed' => 'integer',
            'parameters' => 'array',
        ];
    }

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class, 'world_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_universe_id');
    }

    public function forks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_universe_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(UniverseSnapshotModel::class, 'universe_id');
    }
}
