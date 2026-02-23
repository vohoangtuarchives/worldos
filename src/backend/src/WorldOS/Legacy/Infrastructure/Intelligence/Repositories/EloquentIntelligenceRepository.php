<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Intelligence\Repositories;

use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceReport;
use WorldOS\Legacy\Application\Intelligence\Collections\IntelligenceCollection;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceSource;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EloquentIntelligenceRepository implements IntelligenceRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    public function save(IntelligenceReport $report): IntelligenceReport
    {
        $data = $this->reportToArray($report);
        
        if ($report->id) {
            // Update existing report
            DB::table('intelligence_reports')
                ->where('id', $report->id)
                ->update($data);
        } else {
            // Insert new report
            $data['id'] = $this->generateId();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            DB::table('intelligence_reports')->insert($data);
            
            // Create new report with ID
            $report = new IntelligenceReport(
                $data['id'],
                $report->type,
                $report->source,
                $report->content,
                $report->metadata,
                $report->timestamp,
                $report->accuracy,
                $report->age
            );
        }

        // Clear cache
        $this->clearCacheForWorld($report->metadata['world_id'] ?? null);

        return $report;
    }

    public function findById(string $id): ?IntelligenceReport
    {
        $data = DB::table('intelligence_reports')
            ->where('id', $id)
            ->first();

        return $data ? $this->arrayToReport($data) : null;
    }

    public function findByWorldId(string $worldId): IntelligenceCollection
    {
        $cacheKey = "intelligence_world_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('intelligence_reports')
                ->where('world_id', $worldId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->arrayToCollection($data);
        });
    }

    public function findByType(string $type, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->where('type', $type);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function findBySource(string $sourceId, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->where('source_id', $sourceId);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function findRecent(string $worldId, int $limit = 50): IntelligenceCollection
    {
        $cacheKey = "intelligence_recent_{$worldId}_{$limit}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $limit) {
            $data = DB::table('intelligence_reports')
                ->where('world_id', $worldId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $this->arrayToCollection($data);
        });
    }

    public function findByReliabilityThreshold(float $threshold, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->where('reliability', '>=', $threshold);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function findByUrgency(string $urgency, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->where('urgency', $urgency);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function findByDateRange(\DateTime $start, \DateTime $end, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->whereBetween('created_at', [$start, $end]);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function searchByContent(string $query, string $worldId = null): IntelligenceCollection
    {
        $dbQuery = DB::table('intelligence_reports')
            ->where('content', 'LIKE', "%{$query}%");

        if ($worldId) {
            $dbQuery->where('world_id', $worldId);
        }

        $data = $dbQuery->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function getStatistics(string $worldId): array
    {
        $cacheKey = "intelligence_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('intelligence_reports')
                ->where('world_id', $worldId)
                ->selectRaw('
                    COUNT(*) as total_reports,
                    AVG(reliability) as avg_reliability,
                    AVG(accuracy) as avg_accuracy,
                    COUNT(CASE WHEN urgency = "high" THEN 1 END) as high_urgency,
                    COUNT(CASE WHEN urgency = "medium" THEN 1 END) as medium_urgency,
                    COUNT(CASE WHEN urgency = "low" THEN 1 END) as low_urgency,
                    COUNT(CASE WHEN reliability >= 0.8 THEN 1 END) as reliable_reports,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_reports
                ')
                ->first();

            return [
                'total_reports' => (int) $stats->total_reports,
                'avg_reliability' => (float) $stats->avg_reliability,
                'avg_accuracy' => (float) $stats->avg_accuracy,
                'high_urgency' => (int) $stats->high_urgency,
                'medium_urgency' => (int) $stats->medium_urgency,
                'low_urgency' => (int) $stats->low_urgency,
                'reliable_reports' => (int) $stats->reliable_reports,
                'recent_reports' => (int) $stats->recent_reports,
            ];
        });
    }

    public function getSummary(string $worldId): array
    {
        $cacheKey = "intelligence_summary_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            // Get recent reports for analysis
            $recentReports = $this->findRecent($worldId, 100);
            
            // Analyze patterns
            $patterns = $this->analyzePatterns($recentReports);
            
            // Get threats and opportunities
            $threats = $recentReports->filter(fn($r) => $r->urgency === 'high' && $r->reliability > 0.7)->count();
            $opportunities = $recentReports->filter(fn($r) => $r->type === IntelligenceType::OPPORTUNITY && $r->reliability > 0.6)->count();
            
            return [
                'total_reports' => $recentReports->count(),
                'reliable_reports' => $recentReports->getReliable()->count(),
                'high_urgency' => $recentReports->getHighUrgency()->count(),
                'threats' => $threats,
                'opportunities' => $opportunities,
                'patterns' => $patterns,
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    public function delete(string $id): bool
    {
        $report = $this->findById($id);
        
        if (!$report) {
            return false;
        }

        $deleted = DB::table('intelligence_reports')
            ->where('id', $id)
            ->delete();

        if ($deleted) {
            $this->clearCacheForWorld($report->metadata['world_id'] ?? null);
        }

        return $deleted > 0;
    }

    public function deleteOldReports(\DateTime $cutoff, string $worldId = null): int
    {
        $query = DB::table('intelligence_reports')
            ->where('created_at', '<', $cutoff);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            $this->clearCacheForWorld($worldId);
        }

        return $deleted;
    }

    public function count(string $worldId): int
    {
        return DB::table('intelligence_reports')
            ->where('world_id', $worldId)
            ->count();
    }

    public function exists(string $id): bool
    {
        return DB::table('intelligence_reports')
            ->where('id', $id)
            ->exists();
    }

    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $data = DB::table('intelligence_reports')
            ->where('world_id', $worldId)
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = DB::table('intelligence_reports')
            ->where('world_id', $worldId)
            ->count();

        return [
            'data' => $this->arrayToCollection($data),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }

    public function findByAge(int $maxAgeHours, string $worldId = null): IntelligenceCollection
    {
        $cutoff = Carbon::now()->subHours($maxAgeHours);
        
        $query = DB::table('intelligence_reports')
            ->where('created_at', '>=', $cutoff);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function findByAccuracyDecayThreshold(float $threshold, string $worldId = null): IntelligenceCollection
    {
        $query = DB::table('intelligence_reports')
            ->where('accuracy', '>=', $threshold);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->arrayToCollection($data);
    }

    public function update(IntelligenceReport $report): IntelligenceReport
    {
        return $this->save($report);
    }

    public function bulkSave(IntelligenceCollection $reports): bool
    {
        $data = $reports->map(fn($report) => $this->reportToArray($report))->toArray();
        
        if (empty($data)) {
            return true;
        }

        $success = DB::table('intelligence_reports')->insert($data);

        if ($success) {
            // Clear cache for affected worlds
            $worldIds = $reports->map(fn($r) => $r->metadata['world_id'] ?? null)->unique()->filter();
            foreach ($worldIds as $worldId) {
                $this->clearCacheForWorld($worldId);
            }
        }

        return $success;
    }

    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection
    {
        $query = DB::table('intelligence_reports')
            ->where('world_id', $worldId);

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function reportToArray(IntelligenceReport $report): array
    {
        return [
            'id' => $report->id,
            'world_id' => $report->metadata['world_id'] ?? null,
            'type' => $report->type->value,
            'source_id' => $report->source->id,
            'source_type' => $report->source->type,
            'source_reliability' => $report->source->reliability,
            'content' => $report->content,
            'metadata' => json_encode($report->metadata),
            'timestamp' => $report->timestamp,
            'accuracy' => $report->accuracy,
            'age' => $report->age,
            'urgency' => $report->metadata['urgency'] ?? 'medium',
            'reliability' => $report->source->reliability,
            'updated_at' => now(),
        ];
    }

    private function arrayToReport(object $data): IntelligenceReport
    {
        $metadata = json_decode($data->metadata ?? '{}', true);
        
        return new IntelligenceReport(
            $data->id,
            IntelligenceType::from($data->type),
            new IntelligenceSource(
                (string) $data->source_type,
                (string) $data->source_id,
                (float) $data->source_reliability
            ),
            $data->content,
            $metadata,
            new \DateTime($data->timestamp),
            $data->accuracy,
            $data->age
        );
    }

    private function arrayToCollection(Collection $data): IntelligenceCollection
    {
        $reports = $data->map(fn($item) => $this->arrayToReport($item));
        
        return new IntelligenceCollection($reports->toArray());
    }

    private function analyzePatterns(IntelligenceCollection $reports): array
    {
        // Simple pattern analysis
        $patterns = [];
        
        // Count by type
        $typeCounts = [];
        foreach ($reports as $report) {
            $type = $report->type->value;
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        
        $patterns['type_distribution'] = $typeCounts;
        
        // Count by source
        $sourceCounts = [];
        foreach ($reports as $report) {
            $source = $report->source->type->value;
            $sourceCounts[$source] = ($sourceCounts[$source] ?? 0) + 1;
        }
        
        $patterns['source_distribution'] = $sourceCounts;
        
        // Recent activity
        $recentActivity = $reports->filter(fn($r) => $r->age < 24)->count();
        $patterns['recent_activity'] = $recentActivity;
        
        return $patterns;
    }

    private function generateId(): string
    {
        return 'intel_' . uniqid() . '_' . time();
    }

    private function clearCacheForWorld(?string $worldId): void
    {
        if ($worldId) {
            Cache::forget("intelligence_world_{$worldId}");
            Cache::forget("intelligence_recent_{$worldId}_50");
            Cache::forget("intelligence_stats_{$worldId}");
            Cache::forget("intelligence_summary_{$worldId}");
        }
    }
}
