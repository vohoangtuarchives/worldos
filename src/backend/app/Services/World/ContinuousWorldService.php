<?php

declare(strict_types=1);

namespace App\Services\World;

use App\Application\World\Actions\TickWorldAction;
use Tuzy\Infrastructure\World\Repositories\WorldRepository;
use Tuzy\Infrastructure\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

final class ContinuousWorldService
{
    private const DEFAULT_TICK_INTERVAL = 5; // seconds
    private const MAX_CONCURRENT_TICKS = 3;
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly TickWorldAction $tickAction,
        private readonly WorldRepository $worldRepository,
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {}

    public function startContinuousOperation(string $worldId, ?int $interval = null): bool
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                Log::error("World {$worldId} not found for continuous operation");
                return false;
            }

            if (!$world->isAutonomous()) {
                Log::warning("World {$worldId} is not autonomous - enabling for continuous operation");
                $world = $world->enableAutonomous();
                $this->worldRepository->save($world);
            }

            $interval = $interval ?? self::DEFAULT_TICK_INTERVAL;
            
            // Store continuous operation state
            $this->setContinuousState($worldId, [
                'running' => true,
                'interval' => $interval,
                'started_at' => now(),
                'last_tick_at' => null,
                'total_ticks' => 0,
                'errors' => 0,
            ]);

            // Queue the first tick
            $this->queueNextTick($worldId, $interval);

            Log::info("Continuous operation started for world {$worldId}", [
                'interval' => $interval,
                'autonomous' => $world->isAutonomous(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to start continuous operation for world {$worldId}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function stopContinuousOperation(string $worldId): bool
    {
        try {
            $state = $this->getContinuousState($worldId);
            
            if (!$state || !$state['running']) {
                Log::info("Continuous operation not running for world {$worldId}");
                return true;
            }

            // Update state
            $this->setContinuousState($worldId, array_merge($state, [
                'running' => false,
                'stopped_at' => now(),
            ]));

            Log::info("Continuous operation stopped for world {$worldId}", [
                'total_ticks' => $state['total_ticks'],
                'duration' => now()->diffInMinutes($state['started_at']),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to stop continuous operation for world {$worldId}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function executeContinuousTick(string $worldId): bool
    {
        try {
            $state = $this->getContinuousState($worldId);
            
            if (!$state || !$state['running']) {
                Log::debug("Continuous operation not active for world {$worldId}");
                return false;
            }

            // Check if we should execute tick (rate limiting)
            if (!$this->shouldExecuteTick($worldId, $state)) {
                return false;
            }

            // Execute tick
            $result = $this->performTick($worldId);
            
            if ($result) {
                // Update state
                $this->updateTickState($worldId);
                
                // Queue next tick
                $this->queueNextTick($worldId, $state['interval']);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Continuous tick failed for world {$worldId}", [
                'error' => $e->getMessage(),
            ]);
            
            $this->incrementErrorCount($worldId);
            return false;
        }
    }

    public function getContinuousStatus(string $worldId): ?array
    {
        $state = $this->getContinuousState($worldId);
        
        if (!$state) {
            return null;
        }

        $world = $this->worldRepository->findById($worldId);
        
        return [
            'world_id' => $worldId,
            'running' => $state['running'],
            'autonomous' => $world?->isAutonomous() ?? false,
            'interval' => $state['interval'],
            'started_at' => $state['started_at'],
            'last_tick_at' => $state['last_tick_at'],
            'stopped_at' => $state['stopped_at'] ?? null,
            'total_ticks' => $state['total_ticks'],
            'errors' => $state['errors'],
            'uptime_percentage' => $this->calculateUptimePercentage($state),
            'ticks_per_minute' => $this->calculateTicksPerMinute($worldId),
            'next_tick_in' => $this->getTimeToNextTick($worldId, $state),
        ];
    }

    public function getAllContinuousStatus(): array
    {
        $statuses = [];
        
        // Get all worlds with continuous state
        $worldIds = $this->getAllContinuousWorldIds();
        
        foreach ($worldIds as $worldId) {
            $status = $this->getContinuousStatus($worldId);
            if ($status) {
                $statuses[] = $status;
            }
        }

        return $statuses;
    }

    public function startAllAutonomousWorlds(?int $interval = null): array
    {
        $results = [];
        $worlds = $this->worldRepository->findAutonomous();
        
        foreach ($worlds as $world) {
            $success = $this->startContinuousOperation($world->id(), $interval);
            $results[$world->id()] = $success;
        }

        Log::info("Started continuous operation for autonomous worlds", [
            'total_worlds' => $worlds->count(),
            'successful' => count(array_filter($results)),
            'interval' => $interval ?? self::DEFAULT_TICK_INTERVAL,
        ]);

        return $results;
    }

    public function stopAllWorlds(): array
    {
        $results = [];
        $worldIds = $this->getAllContinuousWorldIds();
        
        foreach ($worldIds as $worldId) {
            $success = $this->stopContinuousOperation($worldId);
            $results[$worldId] = $success;
        }

        Log::info("Stopped continuous operation for all worlds", [
            'total_worlds' => count($worldIds),
            'successful' => count(array_filter($results)),
        ]);

        return $results;
    }

    private function performTick(string $worldId): bool
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            $characters = $this->characterRepository->findByWorldId($worldId);

            $result = $this->tickAction->execute($world, collect($characters));

            Log::debug("Continuous tick executed for world {$worldId}", [
                'tick' => $result->tick,
                'entropy' => $result->entropy->value(),
                'shock_events' => count($result->shockEvents),
                'deaths' => $result->getDeathCount(),
                'execution_time' => $result->executionTime,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Tick execution failed for world {$worldId}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function shouldExecuteTick(string $worldId, array $state): bool
    {
        // Check rate limiting
        $lastTickKey = "continuous_tick_last_{$worldId}";
        $lastTickAt = Cache::get($lastTickKey);
        
        if ($lastTickAt) {
            $secondsSinceLastTick = now()->diffInSeconds($lastTickAt);
            if ($secondsSinceLastTick < $state['interval']) {
                return false;
            }
        }

        // Check concurrent tick limit
        $concurrentKey = "continuous_concurrent_{$worldId}";
        $concurrentCount = Cache::get($concurrentKey, 0);
        
        if ($concurrentCount >= self::MAX_CONCURRENT_TICKS) {
            Log::debug("Max concurrent ticks reached for world {$worldId}");
            return false;
        }

        return true;
    }

    private function queueNextTick(string $worldId, int $interval): void
    {
        // Update last tick time
        $lastTickKey = "continuous_tick_last_{$worldId}";
        Cache::put($lastTickKey, now(), self::CACHE_TTL);

        // Queue next tick
        Queue::later(
            now()->addSeconds($interval),
            new \App\Jobs\ContinuousWorldTickJob($worldId)
        );

        Log::debug("Next tick queued for world {$worldId}", [
            'interval' => $interval,
            'next_tick_at' => now()->addSeconds($interval)->toISOString(),
        ]);
    }

    private function setContinuousState(string $worldId, array $state): void
    {
        $key = "continuous_state_{$worldId}";
        Cache::put($key, $state, self::CACHE_TTL);
    }

    private function getContinuousState(string $worldId): ?array
    {
        $key = "continuous_state_{$worldId}";
        return Cache::get($key);
    }

    private function updateTickState(string $worldId): void
    {
        $state = $this->getContinuousState($worldId);
        
        if ($state) {
            $this->setContinuousState($worldId, array_merge($state, [
                'last_tick_at' => now(),
                'total_ticks' => $state['total_ticks'] + 1,
            ]));
        }
    }

    private function incrementErrorCount(string $worldId): void
    {
        $state = $this->getContinuousState($worldId);
        
        if ($state) {
            $this->setContinuousState($worldId, array_merge($state, [
                'errors' => $state['errors'] + 1,
            ]));
        }
    }

    private function getAllContinuousWorldIds(): array
    {
        // This would scan cache for all continuous_state_* keys
        // For now, return empty array
        return [];
    }

    private function calculateUptimePercentage(array $state): float
    {
        if (!$state['started_at']) {
            return 0.0;
        }

        $totalTime = now()->diffInSeconds($state['started_at']);
        $expectedTicks = $totalTime / $state['interval'];
        
        if ($expectedTicks === 0) {
            return 100.0;
        }

        return ($state['total_ticks'] / $expectedTicks) * 100;
    }

    private function calculateTicksPerMinute(string $worldId): float
    {
        $state = $this->getContinuousState($worldId);
        
        if (!$state || !$state['last_tick_at']) {
            return 0.0;
        }

        $minutesSinceStart = now()->diffInMinutes($state['started_at']);
        
        if ($minutesSinceStart === 0) {
            return 0.0;
        }

        return $state['total_ticks'] / $minutesSinceStart;
    }

    private function getTimeToNextTick(string $worldId, array $state): int
    {
        if (!$state['running'] || !$state['last_tick_at']) {
            return 0;
        }

        $secondsSinceLastTick = now()->diffInSeconds($state['last_tick_at']);
        $timeToNextTick = max(0, $state['interval'] - $secondsSinceLastTick);
        
        return $timeToNextTick;
    }
}
