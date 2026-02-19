<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\ValueObjects\WorldSnapshot;
use App\Domains\Runtime\UniverseRuntimeService;
use App\Domains\Saga\Services\SagaService;
use App\Models\UniverseModel;
use App\Models\World;
use App\Domains\Saga\SagaWorld;

/**
 * EpochControlService — freeze, resume, step, rollback the simulation.
 *
 * WorldOS v3: Uses UniverseRuntimeService + SagaService (Universe-centric pipeline).
 */
class EpochControlService
{
    private bool $frozen = false;

    /** @var WorldSnapshot[] */
    private array $snapshotHistory = [];

    /** @var array<array> Action log */
    private array $actionLog = [];

    public function __construct(
        private readonly UniverseRuntimeService $runtimeService,
        private readonly SagaService $sagaService
    ) {
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
     * WorldOS v3: Advances via SagaService (Universe-centric) instead of dispatching legacy RunSagaSimulationJob.
     */
    public function resume(World $world, string $reason = 'manual'): array
    {
        $world->update(['autonomous' => true]);
        $this->frozen = false;

        // V3: Advance saga by 1 tick on resume
        $sagaWorld = SagaWorld::where('world_id', $world->id)->first();
        if ($sagaWorld && $sagaWorld->saga && $sagaWorld->universe_id) {
            $this->sagaService->runBatch($sagaWorld->saga, 1);
        }

        return $this->log('RESUME', $reason, $world->current_epoch ?? 0);
    }

    /**
     * Step exactly one epoch (while frozen).
     * WorldOS v3: Ticks the active Universe via UniverseRuntimeService.
     */
    public function stepOne(World $world): array
    {
        // V3: Find active universe for this world, tick it
        $universe = UniverseModel::where('world_id', $world->id)
            ->where('is_archived', false)
            ->first();

        if ($universe) {
            $result = $this->runtimeService->tick($universe->id);
            return $this->log('STEP_ONE', 'Advanced 1 tick on Universe ' . substr($universe->id, 0, 8), (int) ($result->getAge()));
        }

        return $this->log('STEP_ONE', 'No active universe found for world', $world->current_epoch ?? 0);
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
