<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\Services;

use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Models\World;
use App\Domains\Saga\SagaWorld;
use App\Jobs\RunSagaSimulationJob;

/**
 * EpochControlService — freeze, resume, step, rollback the simulation.
 *
 * Now integrated with World/Saga models to persist state and dispatch jobs.
 */
class EpochControlService
{
    private bool $frozen = false;

    /** @var WorldSnapshot[] */
    private array $snapshotHistory = [];

    /** @var array<array> Action log */
    private array $actionLog = [];

    public function __construct()
    {
    }

    /**
     * Push a snapshot to history (called after each epoch).
     */
    public function recordSnapshot(WorldSnapshot $snapshot): void
    {
        $this->snapshotHistory[] = $snapshot;

        // Keep last 100 snapshots
        if (count($this->snapshotHistory) > 100) {
            array_shift($this->snapshotHistory);
        }
    }

    /**
     * Freeze the simulation.
     */
    public function freeze(World $world, string $reason = 'manual'): array
    {
        $world->update(['autonomous' => false]);
        $this->frozen = true;
        
        return $this->log('FREEZE', $reason, $world->current_epoch ?? 0);
    }

    /**
     * Resume the simulation.
     */
    public function resume(World $world, string $reason = 'manual'): array
    {
        $world->update(['autonomous' => true]);
        $this->frozen = false;

        // Dispatch job if part of a saga
        $sagaWorld = SagaWorld::where('world_id', $world->id)->first();
        if ($sagaWorld && $sagaWorld->saga) {
             RunSagaSimulationJob::dispatch($sagaWorld->saga);
        }

        return $this->log('RESUME', $reason, $world->current_epoch ?? 0);
    }

    /**
     * Step exactly one epoch (while frozen).
     * Returns true if step is allowed.
     */
    public function stepOne(World $world): array
    {
        // For now, we only log this action as "requested"
        // Actual single-step logic requires triggering the SagaRunner for exactly 1 tick
        
        // TODO: Implement actual step logic via SagaRunner or Job
        
        return $this->log('STEP_ONE', 'manual single-epoch advance', $world->current_epoch ?? 0);
    }
    
    /**
     * Alias for stepOne to match WorldHubController usage
     */
    public function stepEpoch(World $world): array
    {
        return $this->stepOne($world);
    }

    /**
     * Rollback to the previous snapshot.
     */
    public function rollback(World $world = null): ?WorldSnapshot
    {
        if (count($this->snapshotHistory) < 2) {
            $this->log('ROLLBACK_FAILED', 'Insufficient history', $world?->current_epoch ?? 0);
            return null;
        }

        // Remove current, return to previous
        array_pop($this->snapshotHistory);
        $restored = end($this->snapshotHistory);

        $this->log('ROLLBACK', "Restored to epoch {$restored->year}", $restored->year);
        
        // TODO: Apply snapshot state to World model if needed

        return $restored;
    }

    /**
     * Check if simulation is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * Get snapshot history.
     */
    public function getSnapshotHistory(): array
    {
        return $this->snapshotHistory;
    }

    /**
     * Get action log.
     */
    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    /**
     * Get the latest snapshot.
     */
    public function getLatestSnapshot(): ?WorldSnapshot
    {
        return empty($this->snapshotHistory) ? null : end($this->snapshotHistory);
    }

    private function log(string $action, string $reason, int $epoch = 0): array
    {
        $entry = [
            'action' => $action,
            'reason' => $reason,
            'epoch' => $epoch,
            'timestamp' => microtime(true),
        ];
        $this->actionLog[] = $entry;
        return $entry;
    }

    public function toArray(): array
    {
        return [
            'frozen' => $this->frozen,
            'snapshot_count' => count($this->snapshotHistory),
            'action_log' => $this->actionLog,
        ];
    }
}
