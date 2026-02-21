<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\World\Repositories;

use Tuzy\Domain\World\Aggregates\WorldAggregate;
use Illuminate\Support\Collection;

interface WorldRepository
{
    /**
     * Save world aggregate
     */
    public function save(WorldAggregate $world): WorldAggregate;

    /**
     * Find world by ID
     */
    public function findById(string $id): ?WorldAggregate;

    /**
     * Find world by name
     */
    public function findByName(string $name): ?WorldAggregate;

    /**
     * Get all worlds
     */
    public function findAll(): Collection;

    /**
     * Get autonomous worlds
     */
    public function findAutonomous(): Collection;

    /**
     * Get worlds by preset
     */
    public function findByPreset(string $preset): Collection;

    /**
     * Get worlds with entropy above threshold
     */
    public function findWithEntropyAbove(float $threshold): Collection;

    /**
     * Get worlds with low population
     */
    public function findWithLowPopulation(int $threshold): Collection;

    /**
     * Get worlds needing attention
     */
    public function findNeedingAttention(): Collection;

    /**
     * Get worlds by lifecycle phase
     */
    public function findByLifecyclePhase(string $phase): Collection;

    /**
     * Get world statistics
     */
    public function getStatistics(): array;

    /**
     * Delete world
     */
    public function delete(string $id): bool;

    /**
     * Check if world exists
     */
    public function exists(string $id): bool;

    /**
     * Count total worlds
     */
    public function count(): int;

    /**
     * Count autonomous worlds
     */
    public function countAutonomous(): int;

    /**
     * Get worlds with pagination
     */
    public function paginate(int $page = 1, int $perPage = 20): array;

    /**
     * Get worlds for dashboard
     */
    public function getForDashboard(): Collection;

    /**
     * Update world tick
     */
    public function updateTick(string $worldId, int $tick): bool;

    /**
     * Update world entropy
     */
    public function updateEntropy(string $worldId, float $entropy): bool;

    /**
     * Update world autonomous status
     */
    public function updateAutonomousStatus(string $worldId, bool $autonomous): bool;

    /**
     * Get worlds created in date range
     */
    public function findByDateRange(\DateTime $start, \DateTime $end): Collection;

    /**
     * Search worlds by name or description
     */
    public function search(string $query): Collection;

    /**
     * Get worlds with recent activity
     */
    public function findWithRecentActivity(int $hours = 24): Collection;

    /**
     * Get world performance metrics
     */
    public function getPerformanceMetrics(string $worldId): array;

    /**
     * Bulk update worlds
     */
    public function bulkUpdate(Collection $worlds): bool;

    /**
     * Archive old worlds
     */
    public function archiveOldWorlds(\DateTime $cutoff): int;

    /**
     * Get archived worlds
     */
    public function findArchived(): Collection;

    /**
     * Restore archived world
     */
    public function restore(string $worldId): bool;
}
