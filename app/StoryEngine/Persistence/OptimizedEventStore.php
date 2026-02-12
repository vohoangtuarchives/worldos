<?php

namespace App\StoryEngine\Persistence;

use App\StoryEngine\Events\WorldEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedEventStore
{
    private const CACHE_TTL = 3600; // 1 hour
    private const BATCH_SIZE = 1000;

    /**
     * Append a single event to the store.
     */
    public function append(WorldEvent $event): void
    {
        DB::table('world_events')->insert([
            'timeline_id' => $event->timelineId,
            'chapter' => $event->chapter,
            'tick' => $event->chapter, // Legacy support
            'type' => get_class($event),
            'payload' => json_encode($event->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Invalidate cache for this timeline
        $this->invalidateTimelineCache($event->timelineId);
    }

    /**
     * Append multiple events in a batch for better performance.
     */
    public function appendBatch(array $events): void
    {
        if (empty($events)) {
            return;
        }

        $timelineIds = [];
        $insertData = [];

        foreach ($events as $event) {
            $timelineIds[] = $event->timelineId;
            $insertData[] = [
                'timeline_id' => $event->timelineId,
                'chapter' => $event->chapter,
                'tick' => $event->chapter,
                'type' => get_class($event),
                'payload' => json_encode($event->toArray()),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert for performance
        DB::table('world_events')->insert($insertData);

        // Invalidate caches for all affected timelines
        foreach (array_unique($timelineIds) as $timelineId) {
            $this->invalidateTimelineCache($timelineId);
        }
    }

    /**
     * Load events for a timeline with caching and pagination.
     */
    public function load(string $timelineId, int $limit = null, int $offset = 0): iterable
    {
        $cacheKey = "timeline_events_{$timelineId}_{$limit}_{$offset}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($timelineId, $limit, $offset) {
            $query = DB::table('world_events')
                ->where('timeline_id', $timelineId)
                ->orderBy('chapter');

            if ($limit) {
                $query->limit($limit)->offset($offset);
            }

            return $query->get();
        });
    }

    /**
     * Load events up to a specific chapter (for replay).
     */
    public function loadUpToChapter(string $timelineId, int $chapter): iterable
    {
        $cacheKey = "timeline_events_upto_{$timelineId}_{$chapter}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($timelineId, $chapter) {
            return DB::table('world_events')
                ->where('timeline_id', $timelineId)
                ->where('chapter', '<=', $chapter)
                ->orderBy('chapter')
                ->get();
        });
    }

    /**
     * Get event count for a timeline.
     */
    public function getEventCount(string $timelineId): int
    {
        $cacheKey = "timeline_count_{$timelineId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($timelineId) {
            return DB::table('world_events')
                ->where('timeline_id', $timelineId)
                ->count();
        });
    }

    /**
     * Get events by type for analytics.
     */
    public function getEventsByType(string $timelineId, string $eventType): iterable
    {
        $cacheKey = "timeline_events_type_{$timelineId}_{$eventType}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($timelineId, $eventType) {
            return DB::table('world_events')
                ->where('timeline_id', $timelineId)
                ->where('type', $eventType)
                ->orderBy('chapter')
                ->get();
        });
    }

    /**
     * Create a snapshot of timeline state at a specific chapter.
     */
    public function createSnapshot(string $timelineId, int $chapter, array $stateData): void
    {
        DB::table('world_snapshots')->updateOrInsert(
            ['timeline_id' => $timelineId, 'chapter' => $chapter],
            [
                'state_data' => json_encode($stateData),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->invalidateSnapshotCache($timelineId, $chapter);
    }

    /**
     * Load snapshot data if available.
     */
    public function loadSnapshot(string $timelineId, int $chapter): ?array
    {
        $cacheKey = "timeline_snapshot_{$timelineId}_{$chapter}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($timelineId, $chapter) {
            $snapshot = DB::table('world_snapshots')
                ->where('timeline_id', $timelineId)
                ->where('chapter', '<=', $chapter)
                ->orderBy('chapter', 'desc')
                ->first();

            return $snapshot ? json_decode($snapshot->state_data, true) : null;
        });
    }

    /**
     * Fork a timeline from a specific chapter.
     */
    public function forkTimeline(string $sourceTimelineId, string $newTimelineId, int $fromChapter = null): void
    {
        $query = DB::table('world_events')->where('timeline_id', $sourceTimelineId);
        
        if ($fromChapter) {
            $query->where('chapter', '<=', $fromChapter);
        }

        $events = $query->orderBy('chapter')->get();
        
        if ($events->isEmpty()) {
            Log::warning("No events found to fork timeline {$sourceTimelineId}");
            return;
        }

        // Batch insert forked events
        $insertData = [];
        foreach ($events as $event) {
            $insertData[] = [
                'timeline_id' => $newTimelineId,
                'chapter' => $event->chapter,
                'tick' => $event->tick,
                'type' => $event->type,
                'payload' => $event->payload,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in batches to avoid memory issues
        foreach (array_chunk($insertData, self::BATCH_SIZE) as $batch) {
            DB::table('world_events')->insert($batch);
        }

        Log::info("Timeline {$sourceTimelineId} forked to {$newTimelineId} with " . count($insertData) . " events");
    }

    /**
     * Clear old events to manage storage (archiving).
     */
    public function archiveOldEvents(string $timelineId, int $keepLastChapters = 100): int
    {
        $totalEvents = $this->getEventCount($timelineId);
        
        if ($totalEvents <= $keepLastChapters) {
            return 0;
        }

        $cutoffChapter = DB::table('world_events')
            ->where('timeline_id', $timelineId)
            ->orderBy('chapter', 'desc')
            ->offset($keepLastChapters)
            ->limit(1)
            ->value('chapter');

        if (!$cutoffChapter) {
            return 0;
        }

        $deletedCount = DB::table('world_events')
            ->where('timeline_id', $timelineId)
            ->where('chapter', '<', $cutoffChapter)
            ->delete();

        $this->invalidateTimelineCache($timelineId);
        
        Log::info("Archived {$deletedCount} old events from timeline {$timelineId}");
        
        return $deletedCount;
    }

    /**
     * Get performance metrics for monitoring.
     */
    public function getMetrics(): array
    {
        return [
            'total_events' => DB::table('world_events')->count(),
            'total_timelines' => DB::table('world_events')->distinct('timeline_id')->count('timeline_id'),
            'avg_events_per_timeline' => DB::table('world_events')
                ->selectRaw('COUNT(*) / COUNT(DISTINCT timeline_id) as avg')
                ->value('avg'),
            'largest_timeline' => DB::table('world_events')
                ->select('timeline_id', DB::raw('COUNT(*) as count'))
                ->groupBy('timeline_id')
                ->orderBy('count', 'desc')
                ->first(),
        ];
    }

    /**
     * Invalidate timeline cache.
     */
    private function invalidateTimelineCache(string $timelineId): void
    {
        // Pattern-based cache invalidation
        $pattern = "timeline_events_{$timelineId}_*";
        // Note: In a real implementation, you'd use cache tags or a more sophisticated invalidation
        Cache::forget("timeline_events_{$timelineId}_");
        Cache::forget("timeline_count_{$timelineId}");
    }

    /**
     * Invalidate snapshot cache.
     */
    private function invalidateSnapshotCache(string $timelineId, int $chapter): void
    {
        Cache::forget("timeline_snapshot_{$timelineId}_{$chapter}");
    }
}
