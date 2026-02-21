<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Character\Repositories;

use Tuzy\Domain\Character\Aggregates\CharacterSurvivalAggregate;
use Illuminate\Support\Collection;

interface CharacterSurvivalRepository
{
    /**
     * Save character survival aggregate
     */
    public function save(CharacterSurvivalAggregate $character): CharacterSurvivalAggregate;

    /**
     * Find character survival by ID
     */
    public function findById(string $id): ?CharacterSurvivalAggregate;

    /**
     * Find characters by world ID
     */
    public function findByWorldId(string $worldId): Collection;

    /**
     * Find alive characters by world ID
     */
    public function findAliveByWorldId(string $worldId): Collection;

    /**
     * Find dead characters by world ID
     */
    public function findDeadByWorldId(string $worldId): Collection;

    /**
     * Find characters by faction
     */
    public function findByFaction(string $faction, string $worldId = null): Collection;

    /**
     * Find characters by location
     */
    public function findByLocation(string $location, string $worldId = null): Collection;

    /**
     * Find characters by narrative weight
     */
    public function findByNarrativeWeight(float $minWeight, float $maxWeight = null, string $worldId = null): Collection;

    /**
     * Find characters at risk of death
     */
    public function findAtRisk(string $worldId, float $riskThreshold = 0.7): Collection;

    /**
     * Find characters with high survival probability
     */
    public function findHighSurvival(string $worldId, float $survivalThreshold = 0.8): Collection;

    /**
     * Get character survival statistics for world
     */
    public function getSurvivalStatistics(string $worldId): array;

    /**
     * Get survival probability distribution
     */
    public function getSurvivalDistribution(string $worldId): array;

    /**
     * Get faction survival statistics
     */
    public function getFactionStatistics(string $worldId): array;

    /**
     * Get location-based survival statistics
     */
    public function getLocationStatistics(string $worldId): array;

    /**
     * Update character survival status
     */
    public function updateSurvivalStatus(string $characterId, bool $isAlive, string $causeOfDeath = null): bool;

    /**
     * Update survival probability
     */
    public function updateSurvivalProbability(string $characterId, float $probability): bool;

    /**
     * Update narrative weight
     */
    public function updateNarrativeWeight(string $characterId, float $weight): bool;

    /**
     * Delete character survival record
     */
    public function delete(string $id): bool;

    /**
     * Check if character exists
     */
    public function exists(string $id): bool;

    /**
     * Count characters by world
     */
    public function countByWorld(string $worldId): int;

    /**
     * Count alive characters by world
     */
    public function countAliveByWorld(string $worldId): int;

    /**
     * Count dead characters by world
     */
    public function countDeadByWorld(string $worldId): int;

    /**
     * Get characters with pagination
     */
    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array;

    /**
     * Get characters for survival analysis
     */
    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection;

    /**
     * Get recent deaths
     */
    public function getRecentDeaths(string $worldId, int $limit = 10): Collection;

    /**
     * Get survival trends
     */
    public function getSurvivalTrends(string $worldId, int $days = 30): array;

    /**
     * Search characters by name or attributes
     */
    public function search(string $query, string $worldId = null): Collection;

    /**
     * Get characters by age (in ticks)
     */
    public function findByAge(int $minAge, int $maxAge = null, string $worldId = null): Collection;

    /**
     * Get characters by risk factors
     */
    public function findByRiskFactors(array $riskFactors, string $worldId = null): Collection;

    /**
     * Bulk update character survival
     */
    public function bulkUpdate(Collection $characters): bool;

    /**
     * Archive old dead characters
     */
    public function archiveOldDeaths(\DateTime $cutoff, string $worldId = null): int;

    /**
     * Get archived characters
     */
    public function findArchived(string $worldId = null): Collection;

    /**
     * Restore archived character
     */
    public function restore(string $characterId): bool;
}
