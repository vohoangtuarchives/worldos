<?php

namespace App\StoryEngine;

use App\StoryEngine\Persistence\OptimizedEventStore;
use App\Services\WorldLawProfileCache;
use App\Domains\World\Services\WorldLawValidator;
use App\Domains\World\ValueObjects\WorldLawProfile;
use App\Exceptions\Simulation\SimulationException;

class OptimizedSimulator
{
    /** @var Seed[] */
    public array $seeds = [];
    public WorldState $world;
    public CharacterState $character;
    
    // Performance optimizations
    protected OptimizedEventStore $eventStore;
    protected WorldLawProfileCache $profileCache;
    protected WorldLawValidator $validator;
    protected string $timelineId;
    protected array $performanceMetrics = [];

    // Caching
    protected ?WorldLawProfile $cachedProfile = null;
    protected array $factionCache = [];

    public function __construct(string $timelineId = 'simulation_test')
    {
        $this->timelineId = $timelineId;
        $this->eventStore = new OptimizedEventStore();
        $this->profileCache = new WorldLawProfileCache();
        $this->validator = new WorldLawValidator();
        
        $this->world = new WorldState();
        $this->character = new CharacterState();

        $this->initializeFactions();
        $this->seeds[] = new Seed(SeedTransition::TYPE_POWER_GAP, 'personal', 5);
    }

    /**
     * Initialize factions with optimized loading.
     */
    protected function initializeFactions(): void
    {
        $worldId = $this->world->id ?? null;

        if ($worldId) {
            $this->loadFactionsFromDatabase($worldId);
        } else {
            $this->useDefaultFactions();
        }
    }

    /**
     * Load factions from database with eager loading and caching.
     */
    protected function loadFactionsFromDatabase(int $worldId): void
    {
        // Check cache first
        if (isset($this->factionCache[$worldId])) {
            $this->world->factions = $this->factionCache[$worldId];
            return;
        }

        // Optimized query with eager loading
        $dbFactions = \App\Models\Faction::where('world_id', $worldId)
            ->with(['relations', 'attributes']) // Eager load related data
            ->get();

        $factionStates = [];
        foreach ($dbFactions as $faction) {
            $state = new FactionState((string)$faction->id, $faction->name, $faction->type);
            
            // Hydrate attributes efficiently
            if ($faction->attributes) {
                $this->hydrateFactionState($state, $faction->attributes);
            }

            $factionStates[] = $state;
        }

        // Cache the result
        $this->factionCache[$worldId] = $factionStates;
        $this->world->factions = $factionStates;
    }

    /**
     * Use default factions for testing/legacy scenarios.
     */
    protected function useDefaultFactions(): void
    {
        $this->world->factions = [
            new FactionState('sect_1', 'Azure Cloud Sect', 'Sect'),
            new FactionState('clan_1', 'Iron Blood Clan', 'Clan'),
            new FactionState('guild_1', 'Golden Pavilion', 'Guild'),
        ];
    }

    /**
     * Hydrate faction state from attributes.
     */
    protected function hydrateFactionState(FactionState $state, array $attributes): void
    {
        if (isset($attributes['cohesion'])) {
            $state->cohesion = $attributes['cohesion'];
        }
        
        // Add other attribute hydrations as needed
        if (isset($attributes['economy'])) {
            $state->economy = $attributes['economy'];
        }
        
        if (isset($attributes['military_power'])) {
            $state->militaryPower = $attributes['military_power'];
        }
    }

    /**
     * Run simulation with performance monitoring.
     */
    public function run(int $chapters): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $metrics = [];
        $this->performanceMetrics = [
            'start_time' => $startTime,
            'start_memory' => $startMemory,
            'chapter_times' => [],
            'memory_peaks' => [],
        ];

        // Preload world law profile for performance
        $this->preloadWorldLawProfile();

        // Build optimized pipeline
        $pipeline = $this->buildOptimizedPipeline();

        for ($i = 1; $i <= $chapters; $i++) {
            $chapterStartTime = microtime(true);
            
            $chapterMetrics = $this->runChapter($pipeline, $i);
            
            $chapterEndTime = microtime(true);
            $this->performanceMetrics['chapter_times'][$i] = $chapterEndTime - $chapterStartTime;
            $this->performanceMetrics['memory_peaks'][$i] = memory_get_peak_usage(true);
            
            $metrics[] = $chapterMetrics;

            // Performance checkpoint every 10 chapters
            if ($i % 10 === 0) {
                $this->performanceCheckpoint($i);
            }
        }

        $this->performanceMetrics['end_time'] = microtime(true);
        $this->performanceMetrics['end_memory'] = memory_get_usage(true);
        $this->performanceMetrics['total_time'] = $this->performanceMetrics['end_time'] - $startTime;
        $this->performanceMetrics['memory_used'] = $this->performanceMetrics['end_memory'] - $startMemory;

        $this->logPerformanceMetrics();

        return $metrics;
    }

    /**
     * Preload world law profile for better performance.
     */
    protected function preloadWorldLawProfile(): void
    {
        $worldId = $this->world->id ?? null;
        
        if ($worldId) {
            $this->cachedProfile = $this->profileCache->getProfile($worldId);
        } else {
            $this->cachedProfile = $this->profileCache->getDefaultProfile();
        }
    }

    /**
     * Build optimized simulation pipeline.
     */
    protected function buildOptimizedPipeline(): \App\StoryEngine\Simulation\SimulationPipeline
    {
        $pipeline = new \App\StoryEngine\Simulation\SimulationPipeline();
        
        return $pipeline
            ->addPhase(new \App\StoryEngine\Simulation\Phases\PhysicsPhase())
            ->addPhase(new \App\StoryEngine\Simulation\Phases\SeedSelectionPhase())
            ->addPhase(new \App\StoryEngine\Simulation\Phases\UnifiedRulePhase($this->validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\FactionActionPhase($this->validator, $this->eventStore))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\EconomicPhase($this->validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\BalancingPhase($this->validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\MetricsPhase());
    }

    /**
     * Run a single chapter with optimizations.
     */
    protected function runChapter(\App\StoryEngine\Simulation\SimulationPipeline $pipeline, int $chapter): array
    {
        // Check kill switch and safe mode
        $this->checkWorldStatus();

        // Create optimized context
        $context = new \App\StoryEngine\Simulation\SimulationContext(
            $this->world,
            $this->character,
            $this->seeds,
            $this->timelineId,
            $chapter,
            $this->world->id ?? null,
            $this->isInSafeMode()
        );

        // Inject cached profile for performance
        if ($this->cachedProfile) {
            $context->worldLawProfile = $this->cachedProfile;
        }

        // Execute pipeline
        $pipeline->run($context);

        // Sync state back
        $this->seeds = $context->seeds;
        
        return $context->metrics;
    }

    /**
     * Check world status for kill switch and safe mode.
     */
    protected function checkWorldStatus(): void
    {
        $worldId = $this->world->id ?? null;
        
        if (!$worldId) {
            return;
        }

        $freshWorld = \App\Models\World::find($worldId);
        if (!$freshWorld) {
            return;
        }

        if ($freshWorld->health_status === \App\Domains\World\Enums\WorldHealthStatus::HALTED) {
            throw SimulationException::stateCorruption(
                'Simulation halted by kill switch',
                ['world_id' => $worldId, 'status' => $freshWorld->health_status]
            );
        }

        if ($freshWorld->status === 'SAFE_MODE') {
            $this->enableSafeMode();
        }
    }

    /**
     * Check if simulation is in safe mode.
     */
    protected function isInSafeMode(): bool
    {
        // Implementation depends on how safe mode is tracked
        return false; // Placeholder
    }

    /**
     * Enable safe mode for simulation.
     */
    protected function enableSafeMode(): void
    {
        // Implementation for safe mode
        Log::warning("Simulation {$this->timelineId} entering safe mode");
    }

    /**
     * Performance checkpoint for monitoring.
     */
    protected function performanceCheckpoint(int $chapter): void
    {
        $currentTime = microtime(true);
        $elapsedTime = $currentTime - $this->performanceMetrics['start_time'];
        $currentMemory = memory_get_usage(true);
        $memoryUsed = $currentMemory - $this->performanceMetrics['start_memory'];

        Log::info("Simulation checkpoint", [
            'timeline' => $this->timelineId,
            'chapter' => $chapter,
            'elapsed_time' => $elapsedTime,
            'memory_used' => $memoryUsed,
            'memory_peak' => memory_get_peak_usage(true),
            'avg_chapter_time' => $elapsedTime / $chapter,
        ]);

        // Check for performance warnings
        if ($elapsedTime > 300) { // 5 minutes
            Log::warning("Simulation taking longer than expected", [
                'timeline' => $this->timelineId,
                'chapter' => $chapter,
                'elapsed_time' => $elapsedTime,
            ]);
        }

        if ($memoryUsed > 512 * 1024 * 1024) { // 512MB
            Log::warning("High memory usage detected", [
                'timeline' => $this->timelineId,
                'chapter' => $chapter,
                'memory_used' => $memoryUsed,
            ]);
        }
    }

    /**
     * Log comprehensive performance metrics.
     */
    protected function logPerformanceMetrics(): void
    {
        Log::info("Simulation completed", [
            'timeline' => $this->timelineId,
            'total_time' => $this->performanceMetrics['total_time'],
            'memory_used' => $this->performanceMetrics['memory_used'],
            'peak_memory' => max($this->performanceMetrics['memory_peaks']),
            'avg_chapter_time' => array_sum($this->performanceMetrics['chapter_times']) / count($this->performanceMetrics['chapter_times']),
            'slowest_chapter' => max($this->performanceMetrics['chapter_times']),
            'fastest_chapter' => min($this->performanceMetrics['chapter_times']),
        ]);
    }

    /**
     * Get performance metrics for analysis.
     */
    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    /**
     * Clear caches to free memory.
     */
    public function clearCaches(): void
    {
        $this->factionCache = [];
        $this->cachedProfile = null;
        
        // Clear event store caches if needed
        // $this->eventStore->clearCaches();
    }

    /**
     * Replay timeline with optimizations.
     */
    public function replay(string $timelineId, int $targetChapter = null): WorldState
    {
        $startTime = microtime(true);
        
        // Check for snapshot first
        if ($targetChapter) {
            $snapshot = $this->eventStore->loadSnapshot($timelineId, $targetChapter);
            if ($snapshot) {
                Log::info("Using snapshot for timeline replay", [
                    'timeline' => $timelineId,
                    'chapter' => $targetChapter,
                ]);
                return WorldState::fromArray($snapshot);
            }
        }

        // Load events up to target chapter
        $events = $this->eventStore->loadUpToChapter($timelineId, $targetChapter ?? PHP_INT_MAX);
        
        $worldState = new WorldState();
        $eventCount = 0;

        foreach ($events as $event) {
            $this->applyEventToState($event, $worldState);
            $eventCount++;
        }

        $endTime = microtime(true);
        
        Log::info("Timeline replay completed", [
            'timeline' => $timelineId,
            'events_processed' => $eventCount,
            'time_taken' => $endTime - $startTime,
            'target_chapter' => $targetChapter,
        ]);

        return $worldState;
    }

    /**
     * Apply event to world state.
     */
    protected function applyEventToState($event, WorldState $worldState): void
    {
        // Implementation depends on event structure
        // This is a placeholder for the actual event application logic
        $data = json_decode($event->payload, true);
        
        if (isset($data['world_state'])) {
            // Apply world state changes
            foreach ($data['world_state'] as $key => $value) {
                if (property_exists($worldState, $key)) {
                    $worldState->$key = $value;
                }
            }
        }
    }
}
