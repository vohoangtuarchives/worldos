<?php

declare(strict_types=1);

namespace App\Domains\World\Repositories;

use App\Domains\World\Events\ShockEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EloquentShockEventRepository implements ShockEventRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    public function save(ShockEvent $event): ShockEvent
    {
        $data = $this->eventToArray($event);
        
        if ($event->id()) {
            // Update existing event
            DB::table('shock_events')
                ->where('id', $event->id())
                ->update($data);
        } else {
            // Insert new event
            $data['id'] = $this->generateId();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            DB::table('shock_events')->insert($data);
            
            // Create new event with ID
            $event = new ShockEvent(
                $data['id'],
                $data['world_id'],
                $data['type'],
                $data['severity'],
                $data['description'],
                $data['location'],
                $data['affected_regions'],
                $data['impact_level'],
                $data['casualties'],
                $data['metadata'],
                new \DateTime($data['timestamp']),
                new \DateTime($data['created_at']),
                now()
            );
        }

        // Clear cache
        $this->clearCacheForWorld($event->worldId());

        return $event;
    }

    public function findById(string $id): ?ShockEvent
    {
        $cacheKey = "shock_event_{$id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $data = DB::table('shock_events')
                ->where('id', $id)
                ->first();

            return $data ? $this->arrayToEvent($data) : null;
        });
    }

    public function findByWorldId(string $worldId): Collection
    {
        $cacheKey = "shock_events_world_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->orderBy('timestamp', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToEvent($item));
        });
    }

    public function findRecentByWorldId(string $worldId, int $limit = 50): Collection
    {
        $cacheKey = "recent_shock_events_{$worldId}_{$limit}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $limit) {
            $data = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->orderBy('timestamp', 'desc')
                ->limit($limit)
                ->get();

            return $data->map(fn($item) => $this->arrayToEvent($item));
        });
    }

    public function findByType(string $type, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('type', $type);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findBySeverity(float $minSeverity, float $maxSeverity = null, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('severity', '>=', $minSeverity);

        if ($maxSeverity !== null) {
            $query->where('severity', '<=', $maxSeverity);
        }

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('severity', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findByLocation(string $location, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('location', $location);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findByDateRange(\DateTime $start, \DateTime $end, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->whereBetween('timestamp', [$start, $end]);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function getStatistics(string $worldId): array
    {
        $cacheKey = "shock_events_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('
                    COUNT(*) as total_events,
                    AVG(severity) as avg_severity,
                    MAX(severity) as max_severity,
                    MIN(severity) as min_severity,
                    COUNT(CASE WHEN casualties > 0 THEN 1 END) as events_with_casualties,
                    SUM(casualties) as total_casualties,
                    AVG(casualties) as avg_casualties,
                    COUNT(CASE WHEN severity > 0.8 THEN 1 END) as high_severity_events,
                    COUNT(CASE WHEN severity > 0.5 THEN 1 END) as medium_severity_events,
                    COUNT(CASE WHEN severity <= 0.5 THEN 1 END) as low_severity_events,
                    COUNT(CASE WHEN impact_level = "critical" THEN 1 END) as critical_events,
                    COUNT(CASE WHEN impact_level = "major" THEN 1 END) as major_events,
                    COUNT(CASE WHEN impact_level = "minor" THEN 1 END) as minor_events
                ')
                ->first();

            return [
                'total_events' => (int) $stats->total_events,
                'avg_severity' => (float) $stats->avg_severity,
                'max_severity' => (float) $stats->max_severity,
                'min_severity' => (float) $stats->min_severity,
                'events_with_casualties' => (int) $stats->events_with_casualties,
                'total_casualties' => (int) $stats->total_casualties,
                'avg_casualties' => (float) $stats->avg_casualties,
                'high_severity_events' => (int) $stats->high_severity_events,
                'medium_severity_events' => (int) $stats->medium_severity_events,
                'low_severity_events' => (int) $stats->low_severity_events,
                'critical_events' => (int) $stats->critical_events,
                'major_events' => (int) $stats->major_events,
                'minor_events' => (int) $stats->minor_events,
            ];
        });
    }

    public function getTypeDistribution(string $worldId): array
    {
        $cacheKey = "shock_events_type_dist_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $distribution = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('type, COUNT(*) as count, AVG(severity) as avg_severity')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get();

            return $distribution->map(fn($item) => [
                'type' => $item->type,
                'count' => (int) $item->count,
                'avg_severity' => (float) $item->avg_severity,
            ])->toArray();
        });
    }

    public function getSeverityDistribution(string $worldId): array
    {
        $cacheKey = "shock_events_severity_dist_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $distribution = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('
                    CASE 
                        WHEN severity >= 0.8 THEN "critical"
                        WHEN severity >= 0.6 THEN "high"
                        WHEN severity >= 0.4 THEN "medium"
                        WHEN severity >= 0.2 THEN "low"
                        ELSE "minimal"
                    END as severity_range,
                    COUNT(*) as count
                ')
                ->groupBy('severity_range')
                ->orderBy('count', 'desc')
                ->get();

            return $distribution->mapWithKeys(fn($item) => [$item->severity_range => (int) $item->count])->toArray();
        });
    }

    public function getLocationStatistics(string $worldId): array
    {
        $cacheKey = "shock_events_location_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('
                    location,
                    COUNT(*) as total,
                    AVG(severity) as avg_severity,
                    SUM(casualties) as total_casualties,
                    COUNT(CASE WHEN severity > 0.7 THEN 1 END) as high_severity_count
                ')
                ->groupBy('location')
                ->orderBy('total', 'desc')
                ->get();

            return $stats->map(fn($item) => [
                'location' => $item->location,
                'total' => (int) $item->total,
                'avg_severity' => (float) $item->avg_severity,
                'total_casualties' => (int) $item->total_casualties,
                'high_severity_count' => (int) $item->high_severity_count,
            ])->toArray();
        });
    }

    public function findByImpactLevel(string $impactLevel, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('impact_level', $impactLevel);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findHighImpact(string $worldId, float $severityThreshold = 0.8): Collection
    {
        $cacheKey = "high_impact_shock_events_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $severityThreshold) {
            $data = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->where('severity', '>=', $severityThreshold)
                ->orderBy('severity', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToEvent($item));
        });
    }

    public function findWithCasualties(string $worldId): Collection
    {
        $cacheKey = "shock_events_with_casualties_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId) {
            $data = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->where('casualties', '>', 0)
                ->orderBy('casualties', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToEvent($item));
        });
    }

    public function findByAffectedRegions(array $regions, string $worldId = null): Collection
    {
        $query = DB::table('shock_events');

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        // For simplicity, check if any region matches
        foreach ($regions as $region) {
            $query->orWhere('affected_regions', 'LIKE', "%{$region}%");
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('world_id', $worldId);

        if ($since) {
            $query->where('timestamp', '>=', $since);
        }

        return $query->orderBy('timestamp', 'desc')->get()
            ->map(fn($item) => $this->arrayToEvent($item));
    }

    public function getTrends(string $worldId, int $days = 30): array
    {
        $cacheKey = "shock_events_trends_{$worldId}_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId, $days) {
            $cutoff = Carbon::now()->subDays($days);
            
            $trends = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->where('timestamp', '>=', $cutoff)
                ->selectRaw('
                    DATE(timestamp) as date,
                    COUNT(*) as total_events,
                    AVG(severity) as avg_severity,
                    SUM(casualties) as total_casualties,
                    COUNT(DISTINCT type) as unique_types
                ')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            return $trends->map(fn($item) => [
                'date' => $item->date,
                'total_events' => (int) $item->total_events,
                'avg_severity' => (float) $item->avg_severity,
                'total_casualties' => (int) $item->total_casualties,
                'unique_types' => (int) $item->unique_types,
            ])->toArray();
        });
    }

    public function getPredicted(string $worldId): Collection
    {
        $cacheKey = "predicted_shock_events_{$worldId}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId) {
            $data = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->where('predicted', true)
                ->orderBy('probability', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToEvent($item));
        });
    }

    public function getFrequencyAnalysis(string $worldId): array
    {
        $cacheKey = "shock_events_frequency_{$worldId}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId) {
            $analysis = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('
                    type,
                    COUNT(*) as total,
                    AVG(severity) as avg_severity,
                    COUNT(CASE WHEN timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_count,
                    COUNT(CASE WHEN timestamp > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as monthly_count
                ')
                ->groupBy('type')
                ->orderBy('total', 'desc')
                ->get();

            return $analysis->map(fn($item) => [
                'type' => $item->type,
                'total' => (int) $item->total,
                'avg_severity' => (float) $item->avg_severity,
                'recent_count' => (int) $item->recent_count,
                'monthly_count' => (int) $item->monthly_count,
                'frequency' => $item->total > 0 ? ((int) $item->monthly_count / 30) : 0,
            ])->toArray();
        });
    }

    public function search(string $query, string $worldId = null): Collection
    {
        $dbQuery = DB::table('shock_events')
            ->where('description', 'LIKE', "%{$query}%");

        if ($worldId) {
            $dbQuery->where('world_id', $worldId);
        }

        $data = $dbQuery->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $data = DB::table('shock_events')
            ->where('world_id', $worldId)
            ->orderBy('timestamp', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = DB::table('shock_events')
            ->where('world_id', $worldId)
            ->count();

        return [
            'data' => $data->map(fn($item) => $this->arrayToEvent($item)),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }

    public function delete(string $id): bool
    {
        $event = $this->findById($id);
        
        if (!$event) {
            return false;
        }

        $deleted = DB::table('shock_events')
            ->where('id', $id)
            ->delete();

        if ($deleted) {
            Cache::forget("shock_event_{$id}");
            $this->clearCacheForWorld($event->worldId());
        }

        return $deleted > 0;
    }

    public function exists(string $id): bool
    {
        return DB::table('shock_events')
            ->where('id', $id)
            ->exists();
    }

    public function countByWorld(string $worldId): int
    {
        return DB::table('shock_events')
            ->where('world_id', $worldId)
            ->count();
    }

    public function countByType(string $type, string $worldId = null): int
    {
        $query = DB::table('shock_events')
            ->where('type', $type);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        return $query->count();
    }

    public function countBySeverity(float $minSeverity, float $maxSeverity = null, string $worldId = null): int
    {
        $query = DB::table('shock_events')
            ->where('severity', '>=', $minSeverity);

        if ($maxSeverity !== null) {
            $query->where('severity', '<=', $maxSeverity);
        }

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        return $query->count();
    }

    public function findByAge(int $maxAgeHours, string $worldId = null): Collection
    {
        $cutoff = Carbon::now()->subHours($maxAgeHours);
        
        $query = DB::table('shock_events')
            ->where('timestamp', '>=', $cutoff);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function archiveOldEvents(\DateTime $cutoff, string $worldId = null): int
    {
        $query = DB::table('shock_events')
            ->where('timestamp', '<', $cutoff);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $archived = $query->update(['archived' => true]);

        if ($archived > 0) {
            $this->clearCacheForWorld($worldId);
        }

        return $archived;
    }

    public function findArchived(string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->where('archived', true);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('archived_at', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function restore(string $eventId): bool
    {
        $restored = DB::table('shock_events')
            ->where('id', $eventId)
            ->update(['archived' => false]);

        if ($restored) {
            Cache::forget("shock_event_{$eventId}");
            $event = $this->findById($eventId);
            if ($event) {
                $this->clearCacheForWorld($event->worldId());
            }
        }

        return $restored > 0;
    }

    public function bulkSave(Collection $events): bool
    {
        $data = $events->map(fn($event) => $this->eventToArray($event))->toArray();
        
        if (empty($data)) {
            return true;
        }

        $success = DB::transaction(function () use ($data) {
            foreach ($data as $eventData) {
                DB::table('shock_events')->insert($eventData);
            }
            return true;
        });

        if ($success) {
            // Clear cache for affected worlds
            $worldIds = $events->map(fn($e) => $e->worldId())->unique();
            foreach ($worldIds as $worldId) {
                $this->clearCacheForWorld($worldId);
            }
        }

        return $success;
    }

    public function findByTriggerConditions(array $conditions, string $worldId = null): Collection
    {
        // This would require parsing JSON metadata and matching conditions
        $query = DB::table('shock_events');

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        // For now, return events with high severity as they often have trigger conditions
        $query->where('severity', '>', 0.7);

        $data = $query->orderBy('severity', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findBySeasonality(string $season, string $worldId = null): Collection
    {
        // This would require season calculation based on timestamp
        $query = DB::table('shock_events');

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        // For simplicity, return all events
        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function getCorrelationAnalysis(string $worldId): array
    {
        $cacheKey = "shock_events_correlation_{$worldId}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId) {
            // Simple correlation analysis between event types and severity
            $events = $this->findByWorldId($worldId);
            
            $correlations = [];
            $types = $events->pluck('type')->unique();
            
            foreach ($types as $type) {
                $typeEvents = $events->filter(fn($e) => $e->type() === $type);
                $correlations[$type] = [
                    'count' => $typeEvents->count(),
                    'avg_severity' => $typeEvents->avg(fn($e) => $e->severity()),
                    'casualty_rate' => $typeEvents->filter(fn($e) => $e->casualties() > 0)->count() / max(1, $typeEvents->count()),
                ];
            }
            
            return $correlations;
        });
    }

    public function getImpactAssessment(string $worldId): array
    {
        $cacheKey = "shock_events_impact_{$worldId}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId) {
            $stats = DB::table('shock_events')
                ->where('world_id', $worldId)
                ->selectRaw('
                    COUNT(*) as total_events,
                    AVG(severity) as avg_severity,
                    SUM(casualties) as total_casualties,
                    COUNT(CASE WHEN impact_level = "critical" THEN 1 END) as critical_count,
                    COUNT(CASE WHEN impact_level = "major" THEN 1 END) as major_count,
                    COUNT(DISTINCT location) as affected_locations
                ')
                ->first();

            return [
                'total_events' => (int) $stats->total_events,
                'avg_severity' => (float) $stats->avg_severity,
                'total_casualties' => (int) $stats->total_casualties,
                'critical_count' => (int) $stats->critical_count,
                'major_count' => (int) $stats->major_count,
                'affected_locations' => (int) $stats->affected_locations,
                'impact_score' => $this->calculateImpactScore($stats),
            ];
        });
    }

    public function getPredictionData(string $worldId): array
    {
        $cacheKey = "shock_events_prediction_{$worldId}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId) {
            $events = $this->findByWorldId($worldId);
            
            // Simple prediction based on historical patterns
            $typeFrequency = [];
            $severityByType = [];
            
            foreach ($events as $event) {
                $type = $event->type();
                $typeFrequency[$type] = ($typeFrequency[$type] ?? 0) + 1;
                $severityByType[$type][] = $event->severity();
            }
            
            $predictions = [];
            foreach ($typeFrequency as $type => $count) {
                $avgSeverity = array_sum($severityByType[$type]) / count($severityByType[$type]);
                $predictions[$type] = [
                    'frequency' => $count,
                    'avg_severity' => $avgSeverity,
                    'probability' => $count / max(1, $events->count()),
                    'predicted_next_severity' => $avgSeverity,
                ];
            }
            
            return $predictions;
        });
    }

    public function updateImpactAssessment(string $eventId, array $assessment): bool
    {
        $updated = DB::table('shock_events')
            ->where('id', $eventId)
            ->update([
                'impact_assessment' => json_encode($assessment),
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("shock_event_{$eventId}");
            $event = $this->findById($eventId);
            if ($event) {
                $this->clearCacheForWorld($event->worldId());
            }
        }

        return $updated > 0;
    }

    public function getForReporting(string $worldId, \DateTime $since = null): Collection
    {
        return $this->getForAnalysis($worldId, $since);
    }

    public function getDashboardSummary(string $worldId): array
    {
        $cacheKey = "shock_events_dashboard_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId) {
            $recent = $this->findRecentByWorldId($worldId, 10);
            $stats = $this->getStatistics($worldId);
            
            return [
                'recent_events' => $recent->map(fn($e) => [
                    'id' => $e->id(),
                    'type' => $e->type(),
                    'severity' => $e->severity(),
                    'description' => substr($e->description(), 0, 100),
                    'timestamp' => $e->timestamp()->format('Y-m-d H:i:s'),
                ])->toArray(),
                'total_events' => $stats['total_events'],
                'avg_severity' => $stats['avg_severity'],
                'high_severity_count' => $stats['high_severity_events'],
                'total_casualties' => $stats['total_casualties'],
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    public function findByTimeOfDay(int $hour, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->whereRaw('HOUR(timestamp) = ?', [$hour]);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    public function findByDayOfWeek(int $dayOfWeek, string $worldId = null): Collection
    {
        $query = DB::table('shock_events')
            ->whereRaw('DAYOFWEEK(timestamp) = ?', [$dayOfWeek]);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('timestamp', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToEvent($item));
    }

    private function eventToArray(ShockEvent $event): array
    {
        return [
            'id' => $event->id(),
            'world_id' => $event->worldId(),
            'type' => $event->type(),
            'severity' => $event->severity(),
            'description' => $event->description(),
            'location' => $event->location(),
            'affected_regions' => json_encode($event->affectedRegions()),
            'impact_level' => $event->impactLevel(),
            'casualties' => $event->casualties(),
            'metadata' => json_encode($event->metadata()),
            'timestamp' => $event->timestamp()->format('Y-m-d H:i:s'),
            'created_at' => $event->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function arrayToEvent(object $data): ShockEvent
    {
        $affectedRegions = json_decode($data->affected_regions ?? '{}', true);
        $metadata = json_decode($data->metadata ?? '{}', true);
        
        return new ShockEvent(
            $data->id,
            $data->world_id,
            $data->type,
            $data->severity,
            $data->description,
            $data->location,
            $affectedRegions,
            $data->impact_level,
            $data->casualties,
            $metadata,
            new \DateTime($data->timestamp),
            new \DateTime($data->created_at),
            new \DateTime($data->updated_at)
        );
    }

    private function generateId(): string
    {
        return 'shock_' . uniqid() . '_' . time();
    }

    private function clearCacheForWorld(?string $worldId): void
    {
        if ($worldId) {
            Cache::forget("shock_events_world_{$worldId}");
            Cache::forget("recent_shock_events_{$worldId}_50");
            Cache::forget("shock_events_stats_{$worldId}");
            Cache::forget("shock_events_type_dist_{$worldId}");
            Cache::forget("shock_events_severity_dist_{$worldId}");
            Cache::forget("shock_events_location_stats_{$worldId}");
            Cache::forget("high_impact_shock_events_{$worldId}");
            Cache::forget("shock_events_with_casualties_{$worldId}");
            Cache::forget("shock_events_dashboard_{$worldId}");
        }
    }

    private function calculateImpactScore(object $stats): float
    {
        $score = 0.0;
        
        // Base score from total events
        $score += $stats->total_events * 0.1;
        
        // Severity impact
        $score += $stats->avg_severity * 20;
        
        // Casualties impact
        $score += $stats->total_casualties * 5;
        
        // Critical events impact
        $score += $stats->critical_count * 10;
        
        // Major events impact
        $score += $stats->major_count * 5;
        
        // Location spread impact
        $score += $stats->affected_locations * 0.5;
        
        return min(100, $score);
    }
}
