<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Intelligence\Repositories;

use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceReport;
use WorldOS\Legacy\Application\Intelligence\Collections\IntelligenceCollection;
use Illuminate\Support\Collection;

interface IntelligenceRepository
{
    /**
     * Save intelligence report
     */
    public function save(IntelligenceReport $report): IntelligenceReport;

    /**
     * Find intelligence report by ID
     */
    public function findById(string $id): ?IntelligenceReport;

    /**
     * Get all intelligence reports for a world
     */
    public function findByWorldId(string $worldId): IntelligenceCollection;

    /**
     * Get intelligence reports by type
     */
    public function findByType(string $type, string $worldId = null): IntelligenceCollection;

    /**
     * Get intelligence reports by source
     */
    public function findBySource(string $sourceId, string $worldId = null): IntelligenceCollection;

    /**
     * Get recent intelligence reports
     */
    public function findRecent(string $worldId, int $limit = 50): IntelligenceCollection;

    /**
     * Get intelligence reports by reliability threshold
     */
    public function findByReliabilityThreshold(float $threshold, string $worldId = null): IntelligenceCollection;

    /**
     * Get intelligence reports by urgency level
     */
    public function findByUrgency(string $urgency, string $worldId = null): IntelligenceCollection;

    /**
     * Get intelligence reports within date range
     */
    public function findByDateRange(\DateTime $start, \DateTime $end, string $worldId = null): IntelligenceCollection;

    /**
     * Search intelligence reports by content
     */
    public function searchByContent(string $query, string $worldId = null): IntelligenceCollection;

    /**
     * Get intelligence statistics for a world
     */
    public function getStatistics(string $worldId): array;

    /**
     * Get intelligence summary for a world
     */
    public function getSummary(string $worldId): array;

    /**
     * Delete intelligence report
     */
    public function delete(string $id): bool;

    /**
     * Delete old intelligence reports
     */
    public function deleteOldReports(\DateTime $cutoff, string $worldId = null): int;

    /**
     * Count intelligence reports for a world
     */
    public function count(string $worldId): int;

    /**
     * Check if intelligence report exists
     */
    public function exists(string $id): bool;

    /**
     * Get intelligence reports with pagination
     */
    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array;

    /**
     * Get intelligence reports by age
     */
    public function findByAge(int $maxAgeHours, string $worldId = null): IntelligenceCollection;

    /**
     * Get intelligence reports by accuracy decay
     */
    public function findByAccuracyDecayThreshold(float $threshold, string $worldId = null): IntelligenceCollection;

    /**
     * Update intelligence report
     */
    public function update(IntelligenceReport $report): IntelligenceReport;

    /**
     * Bulk save intelligence reports
     */
    public function bulkSave(IntelligenceCollection $reports): bool;

    /**
     * Get intelligence reports for analysis
     */
    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection;
}
