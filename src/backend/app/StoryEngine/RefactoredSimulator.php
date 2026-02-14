<?php

namespace App\StoryEngine;

use App\StoryEngine\Commands\CommandBus;
use App\StoryEngine\Commands\ApplySeedCommand;
use App\StoryEngine\Commands\SimulationCommand;
use App\StoryEngine\Persistence\OptimizedEventStore;
use App\Services\WorldLawProfileCache;
use App\Domains\World\Services\WorldLawValidator;
use App\Domains\World\ValueObjects\WorldLawProfile;

class RefactoredSimulator
{
    private SimulationOrchestrator $orchestrator;
    private StateManager $stateManager;
    private CommandBus $commandBus;
    private OptimizedEventStore $eventStore;
    private WorldLawProfileCache $profileCache;
    private WorldLawValidator $validator;
    
    public array $seeds = [];
    public WorldState $world;
    public CharacterState $character;
    
    protected string $timelineId;
    protected array $refactoringMetrics = [];

    public function __construct(string $timelineId = 'simulation_test')
    {
        $this->timelineId = $timelineId;
        $this->initializeComponents();
        $this->initializeSimulationState();
    }

    /**
     * Initialize all components with dependency injection.
     */
    protected function initializeComponents(): void
    {
        $this->eventStore = new OptimizedEventStore();
        $this->profileCache = new WorldLawProfileCache();
        $this->validator = new WorldLawValidator();
        $this->stateManager = new StateManager($this->profileCache);
        $this->commandBus = new CommandBus();
        $this->orchestrator = new SimulationOrchestrator(
            $this->timelineId,
            $this->eventStore,
            $this->profileCache,
            $this->validator
        );
    }

    /**
     * Initialize simulation state.
     */
    protected function initializeSimulationState(): void
    {
        $this->world = new WorldState();
        $this->character = new CharacterState();
        
        // Initialize world state with factions
        $this->stateManager->initializeWorldState($this->world);
        
        // Add initial seed
        $this->seeds[] = new Seed(SeedTransition::TYPE_POWER_GAP, 'personal', 5);
        
        $this->initializeRefactoringMetrics();
    }

    /**
     * Run simulation using the refactored architecture.
     */
    public function run(int $chapters): array
    {
        $startTime = microtime(true);
        $this->logRefactoringStart();
        
        try {
            // Execute simulation using orchestrator
            $metrics = $this->orchestrator->executeSimulation(
                $this->world,
                $this->character,
                $this->seeds,
                $chapters
            );
            
            // Process command queue
            $this->processCommandQueue();
            
            // Record final metrics
            $this->finalizeRefactoringMetrics($startTime);
            
            return $metrics;
            
        } catch (\Exception $e) {
            $this->handleSimulationError($e);
            throw $e;
        }
    }

    /**
     * Run simulation using command pattern.
     */
    public function runWithCommands(int $chapters): array
    {
        $startTime = microtime(true);
        $this->logRefactoringStart();
        
        try {
            // Create simulation commands for each chapter
            for ($chapter = 1; $chapter <= $chapters; $chapter++) {
                $this->queueChapterCommands($chapter);
            }
            
            // Execute all commands
            $results = $this->commandBus->executeQueue($this->world, $this->character);
            
            // Process results
            $metrics = $this->processCommandResults($results);
            
            // Record final metrics
            $this->finalizeRefactoringMetrics($startTime);
            
            return $metrics;
            
        } catch (\Exception $e) {
            $this->handleSimulationError($e);
            throw $e;
        }
    }

    /**
     * Queue commands for a chapter.
     */
    protected function queueChapterCommands(int $chapter): void
    {
        // Create seed application commands
        foreach ($this->seeds as $seed) {
            $command = new ApplySeedCommand(
                $seed,
                $this->stateManager->preloadWorldLawProfile($this->world),
                $this->validator
            );
            
            $this->commandBus->queue($command);
        }
        
        // Create chapter-specific commands
        $this->queueChapterSpecificCommands($chapter);
    }

    /**
     * Queue chapter-specific commands.
     */
    protected function queueChapterSpecificCommands(int $chapter): void
    {
        // Add custom commands based on chapter number or world state
        if ($chapter % 10 === 0) {
            // Every 10 chapters, add a balancing command
            $this->queueBalancingCommand();
        }
        
        if ($this->world->publicAwareness > 8) {
            // High awareness triggers crisis commands
            $this->queueCrisisCommand();
        }
        
        if ($this->world->powerCenters < 2) {
            // Low power centers trigger opportunity commands
            $this->queueOpportunityCommand();
        }
    }

    /**
     * Queue a balancing command.
     */
    protected function queueBalancingCommand(): void
    {
        $seed = new Seed('TEMPORARY_TRUCE', 'global', 5);
        $command = new ApplySeedCommand(
            $seed,
            $this->stateManager->preloadWorldLawProfile($this->world),
            $this->validator,
            'Apply balancing seed: TEMPORARY_TRUCE'
        );
        
        $this->commandBus->queue($command);
    }

    /**
     * Queue a crisis command.
     */
    protected function queueCrisisCommand(): void
    {
        $seed = new Seed('CRISIS', 'social', 7);
        $command = new ApplySeedCommand(
            $seed,
            $this->stateManager->preloadWorldLawProfile($this->world),
            $this->validator,
            'Apply crisis seed due to high awareness'
        );
        
        $this->commandBus->queue($command);
    }

    /**
     * Queue an opportunity command.
     */
    protected function queueOpportunityCommand(): void
    {
        $seed = new Seed('OPPORTUNITY', 'economic', 6);
        $command = new ApplySeedCommand(
            $seed,
            $this->stateManager->preloadWorldLawProfile($this->world),
            $this->validator,
            'Apply opportunity seed due to low power centers'
        );
        
        $this->commandBus->queue($command);
    }

    /**
     * Process command queue results.
     */
    protected function processCommandResults(array $results): array
    {
        $metrics = [];
        $successfulCommands = 0;
        $failedCommands = 0;
        
        foreach ($results as $result) {
            if ($result['status'] === 'success') {
                $successfulCommands++;
            } else {
                $failedCommands++;
                \Log::warning('Command failed', [
                    'command' => $result['command']->getDescription(),
                    'error' => $result['error'],
                ]);
            }
        }
        
        $metrics[] = [
            'successful_commands' => $successfulCommands,
            'failed_commands' => $failedCommands,
            'total_commands' => count($results),
            'success_rate' => count($results) > 0 ? $successfulCommands / count($results) : 0,
        ];
        
        return $metrics;
    }

    /**
     * Process any remaining commands in the queue.
     */
    protected function processCommandQueue(): void
    {
        if (!empty($this->commandBus->getQueuedCommands())) {
            $this->commandBus->executeQueue($this->world, $this->character);
        }
    }

    /**
     * Handle simulation errors with proper cleanup.
     */
    protected function handleSimulationError(\Exception $e): void
    {
        \Log::error('Simulation failed', [
            'timeline' => $this->timelineId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'world_state' => [
                'public_awareness' => $this->world->publicAwareness,
                'power_centers' => $this->world->powerCenters,
                'faction_count' => count($this->world->factions),
            ],
            'command_bus_stats' => $this->commandBus->getStatistics(),
        ]);
        
        // Cleanup resources
        $this->cleanup();
    }

    /**
     * Cleanup resources after simulation.
     */
    protected function cleanup(): void
    {
        $this->stateManager->clearAllCaches();
        $this->commandBus->clearQueue();
        // Note: Don't clear history as it might be needed for debugging
    }

    /**
     * Initialize refactoring metrics.
     */
    protected function initializeRefactoringMetrics(): void
    {
        $this->refactoringMetrics = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'architecture_components' => [
                'orchestrator' => get_class($this->orchestrator),
                'state_manager' => get_class($this->stateManager),
                'command_bus' => get_class($this->commandBus),
                'event_store' => get_class($this->eventStore),
                'profile_cache' => get_class($this->profileCache),
            ],
        ];
    }

    /**
     * Finalize refactoring metrics.
     */
    protected function finalizeRefactoringMetrics(float $startTime): void
    {
        $this->refactoringMetrics['end_time'] = microtime(true);
        $this->refactoringMetrics['end_memory'] = memory_get_usage(true);
        $this->refactoringMetrics['total_time'] = $this->refactoringMetrics['end_time'] - $startTime;
        $this->refactoringMetrics['memory_used'] = $this->refactoringMetrics['end_memory'] - $this->refactoringMetrics['start_memory'];
        
        $this->logRefactoringMetrics();
    }

    /**
     * Log refactoring start.
     */
    protected function logRefactoringStart(): void
    {
        \Log::info('Starting refactored simulation', [
            'timeline' => $this->timelineId,
            'architecture' => 'refactored',
            'components' => array_keys($this->refactoringMetrics['architecture_components']),
        ]);
    }

    /**
     * Log refactoring metrics.
     */
    protected function logRefactoringMetrics(): void
    {
        \Log::info('Refactored simulation completed', [
            'timeline' => $this->timelineId,
            'total_time' => $this->refactoringMetrics['total_time'],
            'memory_used' => $this->refactoringMetrics['memory_used'],
            'orchestrator_metrics' => $this->orchestrator->getPerformanceMetrics(),
            'command_bus_stats' => $this->commandBus->getStatistics(),
            'state_manager_stats' => $this->stateManager->getCacheStats(),
        ]);
    }

    /**
     * Get comprehensive refactoring metrics.
     */
    public function getRefactoringMetrics(): array
    {
        return array_merge($this->refactoringMetrics, [
            'orchestrator_metrics' => $this->orchestrator->getPerformanceMetrics(),
            'command_bus_stats' => $this->commandBus->getStatistics(),
            'state_manager_stats' => $this->stateManager->getCacheStats(),
        ]);
    }

    /**
     * Get component instances for advanced usage.
     */
    public function getOrchestrator(): SimulationOrchestrator
    {
        return $this->orchestrator;
    }

    public function getStateManager(): StateManager
    {
        return $this->stateManager;
    }

    public function getCommandBus(): CommandBus
    {
        return $this->commandBus;
    }

    public function getEventStore(): OptimizedEventStore
    {
        return $this->eventStore;
    }

    /**
     * Demonstrate the benefits of the refactored architecture.
     */
    public function demonstrateRefactoringBenefits(): array
    {
        $benefits = [];
        
        // 1. Separation of Concerns
        $benefits['separation_of_concerns'] = [
            'orchestrator' => 'Handles simulation flow and coordination',
            'state_manager' => 'Manages world state and caching',
            'command_bus' => 'Handles command execution and transactions',
            'event_store' => 'Handles persistence and replay',
        ];
        
        // 2. Testability
        $benefits['testability'] = [
            'dependency_injection' => 'All components can be mocked',
            'isolated_components' => 'Each component can be tested independently',
            'command_pattern' => 'Commands can be unit tested',
        ];
        
        // 3. Extensibility
        $benefits['extensibility'] = [
            'new_commands' => 'Easy to add new simulation commands',
            'new_phases' => 'Orchestrator can handle new phases',
            'new_strategies' => 'State management can be extended',
        ];
        
        // 4. Performance
        $benefits['performance'] = [
            'caching' => 'State and profile caching reduces database load',
            'batch_operations' => 'Command bus supports batch execution',
            'metrics' => 'Comprehensive performance monitoring',
        ];
        
        // 5. Maintainability
        $benefits['maintainability'] = [
            'single_responsibility' => 'Each class has one clear purpose',
            'clear_interfaces' => 'Well-defined component boundaries',
            'error_handling' => 'Consistent error handling across components',
        ];
        
        return $benefits;
    }
}
