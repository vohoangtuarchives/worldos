<?php

namespace WorldOS\Saga\Domain\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\World;

class SagaWorld extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'saga_id',
        'world_id',
        'universe_id',
        'sequence',
        'archetype_legacy',
        'myth_legacy',
        'status',
        'collapse_context',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'archetype_legacy' => 'array',
        'myth_legacy' => 'array',
        'collapse_context' => 'array',
    ];

    /**
     * Saga world statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_COLLAPSED = 'collapsed';

    /**
     * Get the saga
     */
    public function saga()
    {
        return $this->belongsTo(Saga::class);
    }

    /**
     * Get the world
     */
    public function world()
    {
        return $this->belongsTo(World::class);
    }

    /**
     * WorldOS v3: Get the universe (when saga step = universe)
     */
    public function universe()
    {
        return $this->belongsTo(\App\Models\UniverseModel::class, 'universe_id');
    }

    /**
     * Check if world collapsed
     */
    public function hasCollapsed(): bool
    {
        return $this->status === self::STATUS_COLLAPSED;
    }

    /**
     * Mark as collapsed
     */
    public function markAsCollapsed(array $context): void
    {
        $this->status = self::STATUS_COLLAPSED;
        $this->collapse_context = $context;
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }
}
