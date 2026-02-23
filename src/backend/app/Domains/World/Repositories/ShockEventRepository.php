<?php

declare(strict_types=1);

namespace App\Domains\World\Repositories;

use WorldOS\Blueprint\Domain\Legacy\Event\ShockEvent;
use Illuminate\Support\Collection;

interface ShockEventRepository
{
    /**
     * Save shock event
     */
    public function save(ShockEvent $event): ShockEvent;

    /**
     * Find shock event by ID
     */
    public function findById(string $id): ?ShockEvent;

    /**
     * Get shock events for world
     */
    public function findByWorldId(string $worldId): Collection;

    /**
     * Get recent shock events for world
     */
    public function findRecentByWorldId(string $worldId, int $limit = 50): Collection;

    /**
     * Get shock events by type
     */
    public function findByType(string $type, string $worldId = null): Collection;

    /**
     * Get shock events by severity
     */
    public function findBySeverity(float $minSeverity, float $maxSeverity = null, string $worldId = null): Collection;

    /**
     * Get shock events by location
     */
    public function findByLocation(string $location, string $worldId = null): Collection;

    /**
     * Get shock events within date range
     */
    public function findByDateRange(\DateTime $start, \DateTime $end, string $worldId = null): Collection;

    /**
     * Get shock event statistics for world
     */
    public function getStatistics(string $worldId): array;

    /**
     * Get shock event type distribution
     */
    public function getTypeDistribution(string $worldId): array;

    /**
     * Get shock event severity distribution
     */
    public function getSeverityDistribution(string $worldId): array;

    /**
     * Get location-based shock statistics
     */
    public function getLocationStatistics(string $worldId): array;

    /**
     * Get shock events by impact level
     */
    public function findByImpactLevel(string $impactLevel, string $worldId = null): Collection;

    /**
     * Get high-impact shock events
     */
    public function findHighImpact(string $worldId, float $severityThreshold = 0.8): Collection;

    /**
     * Get shock events with casualties
     */
    public function findWithCasualties(string $worldId): Collection;

    /**
     * Get shock events by affected regions
     */
    public function findByAffectedRegions(array $regions, string $worldId = null): Collection;

    /**
     * Get shock events for analysis
     */
    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection;

    /**
     * Get shock event trends
     */
    public function getTrends(string $worldId, int $days = 30): array;

    /**
     * Get predicted shock events
     */
    public function getPredicted(string $worldId): Collection;

    /**
     * Get shock event frequency analysis
     */
    public function getFrequencyAnalysis(string $worldId): array;

    /**
     * Search shock events by description
     */
    public function search(string $query, string $worldId = null): Collection;

    /**
     * Get shock events with pagination
     */
    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array;

    /**
     * Delete shock event
     */
    public function delete(string $id): bool;

    /**
     * Check if shock event exists
     */
    public function exists(string $id): bool;

    /**
     * Count shock events by world
     */
    public function countByWorld(string $worldId): int;

    /**
     * Count shock events by type
     */
    public function countByType(string $type, string $worldId = null): int;

    /**
     * Count shock events by severity
     */
    public function countBySeverity(float $minSeverity, float $maxSeverity = null, string $worldId = null): int;

    /**
     * Get shock events by age (in hours)
     */
    public function findByAge(int $maxAgeHours, string $worldId = null): Collection;

    /**
     * Archive old shock events
     */
    public function archiveOldEvents(\DateTime $cutoff, string $worldId = null): int;

    /**
     * Get archived shock events
     */
    public function findArchived(string $worldId = null): Collection;

    /**
     * Restore archived shock event
     */
    public function restore(string $eventId): bool;

    /**
     * Bulk save shock events
     */
    public function bulkSave(Collection $events): bool;

    /**
     * Get shock events by trigger conditions
     */
    public function findByTriggerConditions(array $conditions, string $worldId = null): Collection;

    /**
     * Get shock events by seasonality
     */
    public function findBySeasonality(string $season, string $worldId = null): Collection;

    /**
     * Get shock events correlation analysis
     */
    public function getCorrelationAnalysis(string $worldId): array;

    /**
     * Get shock events impact assessment
     */
    public function getImpactAssessment(string $worldId): array;

    /**
     * Get shock events prediction model data
     */
    public function getPredictionData(string $worldId): array;

    /**
     * Update shock event impact assessment
     */
    public function updateImpactAssessment(string $eventId, array $assessment): bool;

    /**
     * Get shock events for reporting
     */
    public function getForReporting(string $worldId, \DateTime $since = null): Collection;

    /**
     * Get shock events summary for dashboard
     */
    public function getDashboardSummary(string $worldId): array;

    /**
     * Get shock events by time of day
     */
    public function findByTimeOfDay(int $hour, string $worldId = null): Collection;

    /**
     * Get shock events by day of week
     */
    public function findByDayOfWeek(int $dayOfWeek, string $worldId = null): Collection;
}
