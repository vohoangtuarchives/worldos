<?php

namespace App\Domains\Saga;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Saga extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'world_count',
        'archetype_focus',
        'carry_legacy',
        'status',
        'current_world_index',
        'metadata',
        'started_at',
        'completed_at',
        'genre'
    ];

    protected $casts = [
        'archetype_focus' => 'array',
        'carry_legacy' => 'boolean',
        'world_count' => 'integer',
        'current_world_index' => 'integer',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Saga statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get saga worlds
     */
    public function sagaWorlds()
    {
        return $this->hasMany(SagaWorld::class)->orderBy('sequence');
    }

    /**
     * Get observations
     */
    public function observations()
    {
        return $this->hasMany(SagaObservation::class);
    }

    /**
     * Check if saga is complete
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if saga is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if saga is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if saga is running
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Get current world
     */
    public function getCurrentWorld(): ?SagaWorld
    {
        return $this->sagaWorlds()
            ->where('sequence', $this->current_world_index)
            ->first();
    }

    /**
     * Get completed worlds
     */
    public function getCompletedWorlds()
    {
        return $this->sagaWorlds()
            ->whereIn('status', [SagaWorld::STATUS_COMPLETED, SagaWorld::STATUS_COLLAPSED])
            ->get();
    }

    /**
     * Get collapse count
     */
    public function getCollapseCount(): int
    {
        return $this->sagaWorlds()
            ->where('status', SagaWorld::STATUS_COLLAPSED)
            ->count();
    }
}
