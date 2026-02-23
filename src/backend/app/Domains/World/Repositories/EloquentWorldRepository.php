<?php

declare(strict_types=1);

namespace App\Domains\World\Repositories;

use App\Domains\World\Aggregates\WorldAggregate;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\EntropyScore;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\GeneVector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EloquentWorldRepository implements WorldRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    public function save(WorldAggregate $world): WorldAggregate
    {
        $data = $this->worldToArray($world);
        
        if ($world->id()) {
            // Update existing world
            DB::table('worlds')
                ->where('id', $world->id())
                ->update($data);
        } else {
            // Insert new world
            $data['id'] = $this->generateId();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            DB::table('worlds')->insert($data);
            
            // Create new world with ID
            $world = new WorldAggregate(
                $data['id'],
                $world->name(),
                $world->preset(),
                $world->geneVector(),
                $world->currentEntropy(),
                $world->currentTick(),
                $world->isAutonomous(),
                $world->lastTickAt(),
                $world->createdAt(),
                now()
            );
        }

        // Clear cache
        $this->clearCache();

        return $world;
    }

    public function findById(string $id): ?WorldAggregate
    {
        $cacheKey = "world_{$id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $data = DB::table('worlds')
                ->where('id', $id)
                ->first();

            return $data ? $this->arrayToWorld($data) : null;
        });
    }

    public function findByName(string $name): ?WorldAggregate
    {
        $data = DB::table('worlds')
            ->where('name', $name)
            ->first();

        return $data ? $this->arrayToWorld($data) : null;
    }

    public function findAll(): Collection
    {
        return Cache::remember('worlds_all', self::CACHE_TTL, function () {
            $data = DB::table('worlds')
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToWorld($item));
        });
    }

    public function findAutonomous(): Collection
    {
        return Cache::remember('worlds_autonomous', self::CACHE_TTL, function () {
            $data = DB::table('worlds')
                ->where('autonomous', true)
                ->orderBy('last_tick_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToWorld($item));
        });
    }

    public function findByPreset(string $preset): Collection
    {
        $cacheKey = "worlds_preset_{$preset}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($preset) {
            $data = DB::table('worlds')
                ->where('preset', $preset)
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToWorld($item));
        });
    }

    public function findWithEntropyAbove(float $threshold): Collection
    {
        $data = DB::table('worlds')
            ->where('entropy', '>', $threshold)
            ->orderBy('entropy', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function findWithLowPopulation(int $threshold): Collection
    {
        // This would require joining with characters table
        $data = DB::table('worlds')
            ->leftJoin('characters', 'worlds.id', '=', 'characters.world_id')
            ->select('worlds.*', DB::raw('COUNT(characters.id) as character_count'))
            ->where('characters.is_alive', true)
            ->groupBy('worlds.id')
            ->having('character_count', '<', $threshold)
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function findNeedingAttention(): Collection
    {
        $data = DB::table('worlds')
            ->where(function ($query) {
                $query->where('entropy', '>', 0.8)
                      ->orWhere('autonomous', false)
                      ->orWhere('last_tick_at', '<', now()->subHours(24));
            })
            ->orderBy('entropy', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function findByLifecyclePhase(string $phase): Collection
    {
        // This would require lifecycle calculation logic
        $data = DB::table('worlds')
            ->where('lifecycle_phase', $phase)
            ->orderBy('created_at', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function getStatistics(): array
    {
        return Cache::remember('worlds_statistics', self::CACHE_TTL, function () {
            $stats = DB::table('worlds')
                ->selectRaw('
                    COUNT(*) as total_worlds,
                    COUNT(CASE WHEN autonomous = true THEN 1 END) as autonomous_worlds,
                    AVG(entropy) as avg_entropy,
                    MAX(entropy) as max_entropy,
                    MIN(entropy) as min_entropy,
                    AVG(current_tick) as avg_tick,
                    MAX(current_tick) as max_tick,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_worlds,
                    COUNT(CASE WHEN last_tick_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as active_worlds
                ')
                ->first();

            return [
                'total_worlds' => (int) $stats->total_worlds,
                'autonomous_worlds' => (int) $stats->autonomous_worlds,
                'avg_entropy' => (float) $stats->avg_entropy,
                'max_entropy' => (float) $stats->max_entropy,
                'min_entropy' => (float) $stats->min_entropy,
                'avg_tick' => (float) $stats->avg_tick,
                'max_tick' => (int) $stats->max_tick,
                'recent_worlds' => (int) $stats->recent_worlds,
                'active_worlds' => (int) $stats->active_worlds,
            ];
        });
    }

    public function delete(string $id): bool
    {
        $deleted = DB::table('worlds')
            ->where('id', $id)
            ->delete();

        if ($deleted) {
            Cache::forget("world_{$id}");
            $this->clearCache();
        }

        return $deleted > 0;
    }

    public function exists(string $id): bool
    {
        return DB::table('worlds')
            ->where('id', $id)
            ->exists();
    }

    public function count(): int
    {
        return DB::table('worlds')->count();
    }

    public function countAutonomous(): int
    {
        return DB::table('worlds')
            ->where('autonomous', true)
            ->count();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $data = DB::table('worlds')
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = DB::table('worlds')->count();

        return [
            'data' => $data->map(fn($item) => $this->arrayToWorld($item)),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }

    public function getForDashboard(): Collection
    {
        return Cache::remember('worlds_dashboard', 60, function () {
            $data = DB::table('worlds')
                ->select([
                    'id',
                    'name',
                    'preset',
                    'entropy',
                    'current_tick',
                    'autonomous',
                    'last_tick_at',
                    'created_at'
                ])
                ->orderBy('last_tick_at', 'desc')
                ->limit(50)
                ->get();

            return $data->map(fn($item) => $this->arrayToWorld($item));
        });
    }

    public function updateTick(string $worldId, int $tick): bool
    {
        $updated = DB::table('worlds')
            ->where('id', $worldId)
            ->update([
                'current_tick' => $tick,
                'last_tick_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("world_{$worldId}");
            $this->clearCache();
        }

        return $updated > 0;
    }

    public function updateEntropy(string $worldId, float $entropy): bool
    {
        $updated = DB::table('worlds')
            ->where('id', $worldId)
            ->update([
                'entropy' => $entropy,
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("world_{$worldId}");
            $this->clearCache();
        }

        return $updated > 0;
    }

    public function updateAutonomousStatus(string $worldId, bool $autonomous): bool
    {
        $updated = DB::table('worlds')
            ->where('id', $worldId)
            ->update([
                'autonomous' => $autonomous,
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("world_{$worldId}");
            $this->clearCache();
        }

        return $updated > 0;
    }

    public function findByDateRange(\DateTime $start, \DateTime $end): Collection
    {
        $data = DB::table('worlds')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function search(string $query): Collection
    {
        $data = DB::table('worlds')
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function findWithRecentActivity(int $hours = 24): Collection
    {
        $cutoff = Carbon::now()->subHours($hours);
        
        $data = DB::table('worlds')
            ->where('last_tick_at', '>=', $cutoff)
            ->orderBy('last_tick_at', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function getPerformanceMetrics(string $worldId): array
    {
        $cacheKey = "world_metrics_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId) {
            $world = $this->findById($worldId);
            
            if (!$world) {
                return [];
            }

            $tickFrequency = $this->calculateTickFrequency($worldId);
            $entropyRate = $this->calculateEntropyRate($worldId);
            
            return [
                'tick_frequency' => $tickFrequency,
                'entropy_rate' => $entropyRate,
                'performance_score' => $this->calculatePerformanceScore($world, $tickFrequency, $entropyRate),
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    public function bulkUpdate(Collection $worlds): bool
    {
        $data = $worlds->map(fn($world) => $this->worldToArray($world))->toArray();
        
        if (empty($data)) {
            return true;
        }

        $success = DB::transaction(function () use ($data) {
            foreach ($data as $worldData) {
                DB::table('worlds')
                    ->where('id', $worldData['id'])
                    ->update($worldData);
            }
            return true;
        });

        if ($success) {
            $this->clearCache();
        }

        return $success;
    }

    public function archiveOldWorlds(\DateTime $cutoff): int
    {
        $archived = DB::table('worlds')
            ->where('created_at', '<', $cutoff)
            ->where('autonomous', false)
            ->update(['archived' => true]);

        if ($archived > 0) {
            $this->clearCache();
        }

        return $archived;
    }

    public function findArchived(): Collection
    {
        $data = DB::table('worlds')
            ->where('archived', true)
            ->orderBy('archived_at', 'desc')
            ->get();

        return $data->map(fn($item) => $this->arrayToWorld($item));
    }

    public function restore(string $worldId): bool
    {
        $restored = DB::table('worlds')
            ->where('id', $worldId)
            ->update(['archived' => false]);

        if ($restored) {
            Cache::forget("world_{$worldId}");
            $this->clearCache();
        }

        return $restored > 0;
    }

    private function worldToArray(WorldAggregate $world): array
    {
        return [
            'id' => $world->id(),
            'name' => $world->name(),
            'preset' => $world->preset(),
            'gene_vector' => json_encode($world->geneVector()->toArray()),
            'entropy' => $world->currentEntropy()->value(),
            'current_tick' => $world->currentTick(),
            'autonomous' => $world->isAutonomous(),
            'last_tick_at' => $world->lastTickAt(),
            'created_at' => $world->createdAt(),
            'updated_at' => now(),
        ];
    }

    private function arrayToWorld(object $data): WorldAggregate
    {
        $geneVectorData = json_decode($data->gene_vector ?? '{}', true);
        $geneVector = new GeneVector($geneVectorData);
        
        return new WorldAggregate(
            (string) $data->id,
            (string) $data->name,
            (string) ($data->preset ?? 'default'),
            $geneVector,
            new EntropyScore((float) ($data->entropy ?? 0.0)),
            (int) ($data->current_tick ?? 0),
            (bool) $data->autonomous,
            $data->last_tick_at ? new \DateTime($data->last_tick_at) : null,
            new \DateTime($data->created_at),
            new \DateTime($data->updated_at)
        );
    }

    private function generateId(): string
    {
        return 'world_' . uniqid() . '_' . time();
    }

    private function clearCache(): void
    {
        Cache::forget('worlds_all');
        Cache::forget('worlds_autonomous');
        Cache::forget('worlds_dashboard');
        Cache::forget('worlds_statistics');
    }

    private function calculateTickFrequency(string $worldId): float
    {
        // Calculate ticks per hour based on recent activity
        $recentTicks = DB::table('world_tick_history')
            ->where('world_id', $worldId)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return $recentTicks / 24; // ticks per hour
    }

    private function calculateEntropyRate(string $worldId): float
    {
        // Calculate entropy change rate
        $recentData = DB::table('world_entropy_history')
            ->where('world_id', $worldId)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get();

        if ($recentData->count() < 2) {
            return 0.0;
        }

        $firstEntropy = $recentData->first()->entropy;
        $lastEntropy = $recentData->last()->entropy;
        
        return ($lastEntropy - $firstEntropy) / 24; // entropy change per hour
    }

    private function calculatePerformanceScore(WorldAggregate $world, float $tickFrequency, float $entropyRate): float
    {
        // Simple performance score calculation
        $score = 100.0;
        
        // Penalize low tick frequency
        if ($tickFrequency < 1.0) {
            $score -= (1.0 - $tickFrequency) * 20;
        }
        
        // Penalize high entropy rate
        if ($entropyRate > 0.1) {
            $score -= $entropyRate * 30;
        }
        
        // Reward low entropy
        if ($world->currentEntropy()->value() < 0.5) {
            $score += 10;
        }
        
        return max(0, min(100, $score));
    }
}
