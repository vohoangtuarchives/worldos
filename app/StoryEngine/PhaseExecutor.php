<?php

namespace App\StoryEngine;

use App\StoryEngine\Simulation\SimulationPipeline;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Persistence\OptimizedEventStore;
use App\Domains\World\Services\WorldLawValidator;
use App\Exceptions\Simulation\SimulationException;

class PhaseExecutor
{
    private WorldLawValidator $validator;
    private OptimizedEventStore $eventStore;
    private array $phaseMetrics = [];

    public function __construct(WorldLawValidator $validator, OptimizedEventStore $eventStore)
    {
        $this->validator = $validator;
        $this->eventStore = $eventStore;
    }

    /**
     * Execute all phases in the simulation pipeline.
     */
    public function executePipeline(SimulationPipeline $pipeline, SimulationContext $context): void
    {
        $pipelineStartTime = microtime(true);
        
        try {
            // Execute each phase with timing and error handling
            $phases = $pipeline->getPhases();
            
            foreach ($phases as $index => $phase) {
                $this->executePhase($phase, $context, $index + 1);
            }
            
        } catch (\Exception $e) {
            $this->handlePipelineError($e, $context);
        }
        
        $pipelineEndTime = microtime(true);
        $this->recordPipelineMetrics($pipelineEndTime - $pipelineStartTime);
    }

    /**
     * Execute a single phase with error handling and metrics.
     */
    protected function executePhase($phase, SimulationContext $context, int $phaseNumber): void
    {
        $phaseStartTime = microtime(true);
        $phaseName = get_class($phase);
        
        try {
            // Pre-phase validation
            $this->validatePrePhase($phase, $context);
            
            // Execute the phase
            $phase->execute($context);
            
            // Post-phase validation
            $this->validatePostPhase($phase, $context);
            
            // Record phase-specific events
            $this->recordPhaseEvents($phase, $context);
            
        } catch (\Exception $e) {
            $this->handlePhaseError($e, $phase, $context, $phaseNumber);
        }
        
        $phaseEndTime = microtime(true);
        $this->recordPhaseMetrics($phaseName, $phaseEndTime - $phaseStartTime);
    }

    /**
     * Validate pre-conditions for phase execution.
     */
    protected function validatePrePhase($phase, SimulationContext $context): void
    {
        // Check if context is valid
        if (!$context->world) {
            throw SimulationException::pipelineFailure(
                get_class($phase),
                'Invalid world state in context'
            );
        }

        // Check world law compliance if applicable
        if ($this->phaseNeedsWorldLawValidation($phase)) {
            $this->validateWorldLaws($context);
        }

        // Check resource availability
        $this->validateResources($context);
    }

    /**
     * Validate post-conditions after phase execution.
     */
    protected function validatePostPhase($phase, SimulationContext $context): void
    {
        // Validate world state integrity
        $issues = $this->validateWorldStateIntegrity($context->world);
        
        if (!empty($issues)) {
            \Log::warning("World state issues detected after phase", [
                'phase' => get_class($phase),
                'timeline' => $context->timelineId,
                'chapter' => $context->chapter,
                'issues' => $issues,
            ]);
        }

        // Validate seed consistency
        $this->validateSeedConsistency($context);
    }

    /**
     * Record phase-specific events to the event store.
     */
    protected function recordPhaseEvents($phase, SimulationContext $context): void
    {
        $events = $this->extractPhaseEvents($phase, $context);
        
        if (!empty($events)) {
            $this->eventStore->appendBatch($events);
        }
    }

    /**
     * Extract events from phase execution.
     */
    protected function extractPhaseEvents($phase, SimulationContext $context): array
    {
        $events = [];
        $phaseName = get_class($phase);
        
        // Extract world state changes
        if ($context->world) {
            $events[] = $this->createWorldStateEvent($phaseName, $context);
        }
        
        // Extract faction actions
        if (!empty($context->world->factions)) {
            $factionEvents = $this->createFactionEvents($phaseName, $context);
            $events = array_merge($events, $factionEvents);
        }
        
        // Extract seed applications
        if (!empty($context->seeds)) {
            $seedEvents = $this->createSeedEvents($phaseName, $context);
            $events = array_merge($events, $seedEvents);
        }
        
        return $events;
    }

    /**
     * Create world state change event.
     */
    protected function createWorldStateEvent(string $phaseName, SimulationContext $context): array
    {
        return [
            'timeline_id' => $context->timelineId,
            'chapter' => $context->chapter,
            'tick' => $context->chapter,
            'type' => 'world_state_update',
            'payload' => json_encode([
                'phase' => $phaseName,
                'public_awareness' => $context->world->publicAwareness,
                'power_centers' => $context->world->powerCenters,
                'tier_index' => $context->world->tierIndex,
                'timestamp' => now()->toISOString(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create faction action events.
     */
    protected function createFactionEvents(string $phaseName, SimulationContext $context): array
    {
        $events = [];
        
        foreach ($context->world->factions as $faction) {
            $events[] = [
                'timeline_id' => $context->timelineId,
                'chapter' => $context->chapter,
                'tick' => $context->chapter,
                'type' => 'faction_action',
                'payload' => json_encode([
                    'phase' => $phaseName,
                    'faction_id' => $faction->id,
                    'faction_name' => $faction->name,
                    'cohesion' => $faction->cohesion ?? null,
                    'economy' => $faction->economy ?? null,
                    'timestamp' => now()->toISOString(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $events;
    }

    /**
     * Create seed application events.
     */
    protected function createSeedEvents(string $phaseName, SimulationContext $context): array
    {
        $events = [];
        
        foreach ($context->seeds as $seed) {
            $events[] = [
                'timeline_id' => $context->timelineId,
                'chapter' => $context->chapter,
                'tick' => $context->chapter,
                'type' => 'seed_application',
                'payload' => json_encode([
                    'phase' => $phaseName,
                    'seed_type' => $seed->type,
                    'seed_dimension' => $seed->dimension,
                    'severity' => $seed->severity,
                    'timestamp' => now()->toISOString(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $events;
    }

    /**
     * Check if phase requires world law validation.
     */
    protected function phaseNeedsWorldLawValidation($phase): bool
    {
        // Phases that modify world state need validation
        $validatingPhases = [
            \App\StoryEngine\Simulation\Phases\UnifiedRulePhase::class,
            \App\StoryEngine\Simulation\Phases\FactionActionPhase::class,
            \App\StoryEngine\Simulation\Phases\BalancingPhase::class,
        ];
        
        return in_array(get_class($phase), $validatingPhases);
    }

    /**
     * Validate world laws for the current context.
     */
    protected function validateWorldLaws(SimulationContext $context): void
    {
        if (!$context->worldLawProfile) {
            return; // No world law profile to validate against
        }

        // Create claims based on current context
        $claims = $this->extractWorldClaims($context);
        
        // Validate claims against world laws
        $violations = [];
        $isValid = $this->validator->validateClaims($context->worldLawProfile, $claims, $violations);
        
        if (!$isValid) {
            throw SimulationException::stateCorruption(
                'World law violations detected',
                [
                    'timeline' => $context->timelineId,
                    'chapter' => $context->chapter,
                    'violations' => $violations,
                ]
            );
        }
    }

    /**
     * Extract world claims from context for validation.
     */
    protected function extractWorldClaims(SimulationContext $context): array
    {
        $claims = [];
        
        // Extract claims from world state
        if ($context->world->publicAwareness > 8) {
            $claims[] = new \App\Domains\World\ValueObjects\Claim(
                'HIGH_PUBLIC_AWARENESS',
                $context->world->publicAwareness
            );
        }
        
        // Extract claims from factions
        foreach ($context->world->factions as $faction) {
            if (isset($faction->militaryPower) && $faction->militaryPower > 7) {
                $claims[] = new \App\Domains\World\ValueObjects\Claim(
                    'HIGH_MILITARY_POWER',
                    $faction->militaryPower
                );
            }
        }
        
        return $claims;
    }

    /**
     * Validate resource availability for phase execution.
     */
    protected function validateResources(SimulationContext $context): void
    {
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        if ($memoryUsage > 1024 * 1024 * 1024) { // 1GB
            throw SimulationException::resourceExhaustion(
                'memory',
                [
                    'timeline' => $context->timelineId,
                    'chapter' => $context->chapter,
                    'memory_usage' => $memoryUsage,
                ]
            );
        }
        
        // Check execution time
        $executionTime = microtime(true) - ($context->startTime ?? microtime(true));
        if ($executionTime > 600) { // 10 minutes
            throw SimulationException::resourceExhaustion(
                'time',
                [
                    'timeline' => $context->timelineId,
                    'chapter' => $context->chapter,
                    'execution_time' => $executionTime,
                ]
            );
        }
    }

    /**
     * Validate world state integrity.
     */
    protected function validateWorldStateIntegrity(WorldState $world): array
    {
        $issues = [];
        
        // Check basic constraints
        if ($world->publicAwareness < 0 || $world->publicAwareness > 10) {
            $issues[] = "Public awareness out of range: {$world->publicAwareness}";
        }
        
        if ($world->powerCenters < 0) {
            $issues[] = "Negative power centers: {$world->powerCenters}";
        }
        
        // Check faction consistency
        $factionIds = [];
        foreach ($world->factions as $faction) {
            if (in_array($faction->id, $factionIds)) {
                $issues[] = "Duplicate faction ID: {$faction->id}";
            }
            $factionIds[] = $faction->id;
        }
        
        return $issues;
    }

    /**
     * Validate seed consistency.
     */
    protected function validateSeedConsistency(SimulationContext $context): void
    {
        foreach ($context->seeds as $seed) {
            if ($seed->severity < 1 || $seed->severity > 10) {
                throw SimulationException::stateCorruption(
                    "Invalid seed severity: {$seed->severity}",
                    [
                        'seed_type' => $seed->type,
                        'severity' => $seed->severity,
                    ]
                );
            }
        }
    }

    /**
     * Handle phase execution errors.
     */
    protected function handlePhaseError(\Exception $e, $phase, SimulationContext $context, int $phaseNumber): void
    {
        $phaseName = get_class($phase);
        
        \Log::error("Phase execution failed", [
            'phase' => $phaseName,
            'phase_number' => $phaseNumber,
            'timeline' => $context->timelineId,
            'chapter' => $context->chapter,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        throw SimulationException::pipelineFailure(
            $phaseName,
            $e->getMessage(),
            [
                'phase_number' => $phaseNumber,
                'timeline' => $context->timelineId,
                'chapter' => $context->chapter,
            ]
        );
    }

    /**
     * Handle pipeline-level errors.
     */
    protected function handlePipelineError(\Exception $e, SimulationContext $context): void
    {
        \Log::error("Pipeline execution failed", [
            'timeline' => $context->timelineId,
            'chapter' => $context->chapter,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        throw SimulationException::pipelineFailure(
            'simulation_pipeline',
            $e->getMessage(),
            [
                'timeline' => $context->timelineId,
                'chapter' => $context->chapter,
            ]
        );
    }

    /**
     * Record phase execution metrics.
     */
    protected function recordPhaseMetrics(string $phaseName, float $executionTime): void
    {
        if (!isset($this->phaseMetrics[$phaseName])) {
            $this->phaseMetrics[$phaseName] = [
                'executions' => 0,
                'total_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0,
            ];
        }
        
        $metrics = &$this->phaseMetrics[$phaseName];
        $metrics['executions']++;
        $metrics['total_time'] += $executionTime;
        $metrics['min_time'] = min($metrics['min_time'], $executionTime);
        $metrics['max_time'] = max($metrics['max_time'], $executionTime);
    }

    /**
     * Record pipeline execution metrics.
     */
    protected function recordPipelineMetrics(float $executionTime): void
    {
        $this->phaseMetrics['pipeline_total'] = [
            'executions' => 1,
            'total_time' => $executionTime,
            'min_time' => $executionTime,
            'max_time' => $executionTime,
        ];
    }

    /**
     * Get phase execution metrics.
     */
    public function getPhaseMetrics(): array
    {
        $result = [];
        
        foreach ($this->phaseMetrics as $phaseName => $metrics) {
            $result[$phaseName] = [
                'executions' => $metrics['executions'],
                'total_time' => $metrics['total_time'],
                'average_time' => $metrics['total_time'] / $metrics['executions'],
                'min_time' => $metrics['min_time'],
                'max_time' => $metrics['max_time'],
            ];
        }
        
        return $result;
    }

    /**
     * Reset phase metrics.
     */
    public function resetMetrics(): void
    {
        $this->phaseMetrics = [];
    }
}
