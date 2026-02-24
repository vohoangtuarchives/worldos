<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * WorldScar
 *
 * Represents a permanent mark on history that increases world inertia.
 * Rules:
 * - Created when a Myth dies or a major Event concludes.
 * - Cannot be deleted (Immutable History).
 * - Adds weight to the Physics engine (makes change harder).
 *
 * @property string $id
 * @property string $world_id
 * @property ?string $source_myth_id
 * @property ?string $source_event_id
 * @property array $snapshot_data JSONB of the world state at scar creation
 * @property float $inertia_weight Default 1.0
 * @property \Carbon\Carbon $created_at
 */
class WorldScar extends Model
{
    use HasUuids;

    protected $table = 'world_scars';

    protected $fillable = [
        'world_id',
        'source_myth_id',
        'source_event_id',
        'snapshot_data',
        'inertia_weight',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'inertia_weight' => 'float',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(\App\Models\World::class);
    }

    public function sourceMyth(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WorldMyth::class, 'source_myth_id');
    }
}
