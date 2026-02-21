<?php

namespace App\StoryEngine;

use App\StoryEngine\Simulation\SimulationPipeline;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Persistence\OptimizedEventStore;
use App\Services\WorldLawProfileCache;
use App\Domains\World\Services\WorldLawValidator;
use App\Exceptions\Simulation\SimulationException;

class SimulationOrchestrator
{
    private OptimizedEventStore $eventStore;
    private WorldLawProfileCache $profileCache;
    private WorldLawValidator $validator;
    private StateManager $stateManager;
    private PhaseExecutor $phaseExecutor;
    
    protected string $timelineId;
    protected array $performanceMetrics = [];

    public function __construct(
        string $timelineId,
        ?OptimizedEventStore $eventStore = null,
        ?WorldLawProfileCache $profileCache = null,
        ?WorldLawValidator $validator = null
    ) {
        $this->timelineId = $timelineId;
        $this->eventStore = $eventStore ?? new OptimizedEventStore();
        $this->profileCache = $profileCache ?? new WorldLawProfileCache();
        $this->validator = $validator ?? new WorldLawValidator();
        $this->stateManager = new StateManager($this->profileCache);
        $this->phaseExecutor = new PhaseExecutor($this->validator, $this->eventStore);
    }

    /**
     * Execute simulation for specified number of chapters.
     */
    public function executeSimulation(WorldState $world, CharacterState $character, array $seeds, int $chapters): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $this->initializeMetrics($startTime, $startMemory);
        
        // Preload world law profile for performance
        $worldLawProfile = $this->stateManager->preloadWorldLawProfile($world);
        
        // Build simulation pipeline
        $pipeline = $this->buildPipeline();
        
        $metrics = [];
        for ($chapter = 1; $chapter <= $chapters; $chapter++) {
            $chapterMetrics = $this->executeChapter($pipeline, $world, $character, $seeds, $chapter, $worldLawProfile);
            $metrics[] = $chapterMetrics;
            
            // Performance checkpoint every 10 chapters
            if ($chapter % 10 === 0) {
                $this->performanceCheckpoint($chapter);
            }
        }
        
        $this->finalizeMetrics();
        $this->logPerformanceMetrics();
        
        return $metrics;
    }

    /**
     * Execute a single chapter of the simulation.
     */
    protected function executeChapter(
        SimulationPipeline $pipeline,
        WorldState $world,
        CharacterState $character,
        array &$seeds,
        int $chapter,
        ?object $worldLawProfile = null
    ): array {
        $chapterStartTime = microtime(true);
        
        // Check world status before execution
        $this->checkWorldStatus($world);
        
        // Create simulation context
        $context = new SimulationContext(
            $world,
            $character,
            $seeds,
            $this->timelineId,
            $chapter,
            $world->id ?? null,
            $this->isInSafeMode($world)
        );
        
        // Inject world law profile if available
        if ($worldLawProfile) {
            $context->worldLawProfile = $worldLawProfile;
        }
        
        // Execute pipeline phases
        $this->phaseExecutor->executePipeline($pipeline, $context);
        
        // Sync state changes back
        $seeds = $context->seeds;
        
        // Record chapter performance
        $chapterEndTime = microtime(true);
        $this->recordChapterPerformance($chapter, $chapterEndTime - $chapterStartTime);
        
        return $context->metrics;
    }

    /**
     * Build the simulation pipeline with all phases.
     */
    protected function buildPipeline(): SimulationPipeline
    {
        $pipeline = new SimulationPipeline();
        
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
     * Check world status for kill switches and safe mode.
     */
    protected function checkWorldStatus(WorldState $world): void
    {
        $worldId = $world->id ?? null;
        
        if (!$worldId) {
            return;
        }

        $freshWorld = \App\Models\World::find($worldId);
        if (!$freshWorld) {
            return;
        }

        if ($freshWorld->health_status === \Tuzy\Domain\World\ValueObject\WorldHealthStatus::HALTED) {
            throw SimulationException::stateCorruption(
                'Simulation halted by kill switch',
                ['world_id' => $worldId, 'status' => $freshWorld->health_status]
            );
        }
    }

    /**
     * Check if world is in safe mode.
     */
    protected function isInSafeMode(WorldState $world): bool
    {
        $worldId = $world->id ?? null;
        
        if (!$worldId) {
            return false;
        }

        $freshWorld = \App\Models\World::find($worldId);
        return $freshWorld?->status === 'SAFE_MODE' ?? false;
    }

    /**
     * Initialize performance metrics collection.
     */
    protected function initializeMetrics(float $startTime, int $startMemory): void
    {
        $this->performanceMetrics = [
            'start_time' => $startTime,
            'start_memory' => $startMemory,
            'chapter_times' => [],
            'memory_peaks' => [],
        ];
    }

    /**
     * Record performance metrics for a chapter.
     */
    protected function recordChapterPerformance(int $chapter, float $chapterTime): void
    {
        $this->performanceMetrics['chapter_times'][$chapter] = $chapterTime;
        $this->performanceMetrics['memory_peaks'][$chapter] = memory_get_peak_usage(true);
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

        \Log::info("Simulation checkpoint", [
            'timeline' => $this->timelineId,
            'chapter' => $chapter,
            'elapsed_time' => $elapsedTime,
            'memory_used' => $memoryUsed,
            'memory_peak' => memory_get_peak_usage(true),
            'avg_chapter_time' => $elapsedTime / $chapter,
        ]);

        // Performance warnings
        if ($elapsedTime > 300) { // 5 minutes
            \Log::warning("Simulation taking longer than expected", [
                'timeline' => $this->timelineId,
                'chapter' => $chapter,
                'elapsed_time' => $elapsedTime,
            ]);
        }

        if ($memoryUsed > 512 * 1024 * 1024) { // 512MB
            \Log::warning("High memory usage detected", [
                'timeline' => $this->timelineId,
                'chapter' => $chapter,
                'memory_used' => $memoryUsed,
            ]);
        }
    }

    /**
     * Finalize performance metrics collection.
     */
    protected function finalizeMetrics(): void
    {
        $this->performanceMetrics['end_time'] = microtime(true);
        $this->performanceMetrics['end_memory'] = memory_get_usage(true);
        $this->performanceMetrics['total_time'] = $this->performanceMetrics['end_time'] - $this->performanceMetrics['start_time'];
        $this->performanceMetrics['memory_used'] = $this->performanceMetrics['end_memory'] - $this->performanceMetrics['start_memory'];
    }

    /**
     * Log comprehensive performance metrics.
     */
    protected function logPerformanceMetrics(): void
    {
        $chapterTimes = $this->performanceMetrics['chapter_times'];
        
        \Log::info("Simulation completed", [
            'timeline' => $this->timelineId,
            'total_time' => $this->performanceMetrics['total_time'],
            'memory_used' => $this->performanceMetrics['memory_used'],
            'peak_memory' => max($this->performanceMetrics['memory_peaks']),
            'chapters_completed' => count($chapterTimes),
            'avg_chapter_time' => count($chapterTimes) > 0 ? array_sum($chapterTimes) / count($chapterTimes) : 0,
            'slowest_chapter' => count($chapterTimes) > 0 ? max($chapterTimes) : 0,
            'fastest_chapter' => count($chapterTimes) > 0 ? min($chapterTimes) : 0,
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
     * Get the timeline ID.
     */
    public function getTimelineId(): string
    {
        return $this->timelineId;
    }

    /**
     * Get the state manager.
     */
    public function getStateManager(): StateManager
    {
        return $this->stateManager;
    }

    /**
     * Get the phase executor.
     */
    public function getPhaseExecutor(): PhaseExecutor
    {
        return $this->phaseExecutor;
    }
}
