<?php

namespace App\StoryEngine\Commands;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\Exceptions\Simulation\SimulationException;

class CommandBus
{
    private array $executedCommands = [];
    private array $commandQueue = [];
    private int $maxQueueSize = 1000;
    private float $maxExecutionTime = 30.0; // 30 seconds
    private int $maxMemoryUsage = 512 * 1024 * 1024; // 512MB

    /**
     * Execute a single command immediately.
     */
    public function execute(SimulationCommand $command, WorldState $world, CharacterState $character): void
    {
        $this->validateExecutionEnvironment();
        
        if (!$command->validate($world, $character)) {
            throw SimulationException::pipelineFailure(
                'command_validation',
                "Command validation failed: {$command->getDescription()}"
            );
        }

        $startTime = microtime(true);
        
        try {
            $command->execute($world, $character);
            $this->recordExecution($command, $world, $character, $startTime);
        } catch (\Exception $e) {
            $this->handleCommandError($command, $e, $world, $character);
        }
    }

    /**
     * Queue a command for later execution.
     */
    public function queue(SimulationCommand $command): void
    {
        if (count($this->commandQueue) >= $this->maxQueueSize) {
            throw SimulationException::resourceExhaustion(
                'command_queue',
                ['queue_size' => count($this->commandQueue), 'max_size' => $this->maxQueueSize]
            );
        }

        $this->commandQueue[] = $command;
        $this->sortQueueByPriority();
    }

    /**
     * Execute all queued commands.
     */
    public function executeQueue(WorldState $world, CharacterState $character): array
    {
        $results = [];
        $startTime = microtime(true);
        
        while (!empty($this->commandQueue)) {
            // Check execution time limit
            if (microtime(true) - $startTime > $this->maxExecutionTime) {
                throw SimulationException::resourceExhaustion(
                    'execution_time',
                    ['elapsed' => microtime(true) - $startTime, 'limit' => $this->maxExecutionTime]
                );
            }

            $command = array_shift($this->commandQueue);
            
            try {
                $this->execute($command, $world, $character);
                $results[] = ['command' => $command, 'status' => 'success'];
            } catch (\Exception $e) {
                $results[] = ['command' => $command, 'status' => 'error', 'error' => $e->getMessage()];
                
                // Decide whether to continue or stop on error
                if ($this->shouldStopOnError($e)) {
                    break;
                }
            }
        }
        
        return $results;
    }

    /**
     * Execute multiple commands as a transaction.
     */
    public function executeTransaction(array $commands, WorldState $world, CharacterState $character): void
    {
        $originalWorld = clone $world;
        $originalCharacter = clone $character;
        $executedCommands = [];

        try {
            foreach ($commands as $command) {
                $this->execute($command, $world, $character);
                $executedCommands[] = $command;
            }
        } catch (\Exception $e) {
            // Rollback all executed commands
            $this->rollbackTransaction($executedCommands, $originalWorld, $originalCharacter);
            
            throw SimulationException::pipelineFailure(
                'command_transaction',
                "Transaction failed and rolled back: " . $e->getMessage(),
                ['commands_executed' => count($executedCommands)]
            );
        }
    }

    /**
     * Undo the last executed command.
     */
    public function undo(WorldState $world, CharacterState $character): bool
    {
        if (empty($this->executedCommands)) {
            return false;
        }

        $lastCommand = array_pop($this->executedCommands);
        
        if (!$lastCommand->canUndo()) {
            // Put it back since we can't undo it
            $this->executedCommands[] = $lastCommand;
            return false;
        }

        try {
            $lastCommand->undo($world, $character);
            return true;
        } catch (\Exception $e) {
            // Put it back since undo failed
            $this->executedCommands[] = $lastCommand;
            throw SimulationException::pipelineFailure(
                'command_undo',
                "Failed to undo command: " . $e->getMessage(),
                ['command' => $lastCommand->getDescription()]
            );
        }
    }

    /**
     * Undo multiple commands.
     */
    public function undoMultiple(int $count, WorldState $world, CharacterState $character): int
    {
        $undoneCount = 0;
        
        for ($i = 0; $i < $count; $i++) {
            if (!$this->undo($world, $character)) {
                break;
            }
            $undoneCount++;
        }
        
        return $undoneCount;
    }

    /**
     * Get execution history.
     */
    public function getExecutionHistory(): array
    {
        return $this->executedCommands;
    }

    /**
     * Get queued commands.
     */
    public function getQueuedCommands(): array
    {
        return $this->commandQueue;
    }

    /**
     * Clear execution history.
     */
    public function clearHistory(): void
    {
        $this->executedCommands = [];
    }

    /**
     * Clear command queue.
     */
    public function clearQueue(): void
    {
        $this->commandQueue = [];
    }

    /**
     * Get command bus statistics.
     */
    public function getStatistics(): array
    {
        $commandTypes = [];
        
        foreach ($this->executedCommands as $command) {
            $type = $command->getType();
            if (!isset($commandTypes[$type])) {
                $commandTypes[$type] = 0;
            }
            $commandTypes[$type]++;
        }

        return [
            'executed_commands' => count($this->executedCommands),
            'queued_commands' => count($this->commandQueue),
            'command_types' => $commandTypes,
            'total_execution_cost' => $this->calculateTotalExecutionCost(),
            'memory_usage' => memory_get_usage(true),
        ];
    }

    /**
     * Validate execution environment.
     */
    protected function validateExecutionEnvironment(): void
    {
        $memoryUsage = memory_get_usage(true);
        
        if ($memoryUsage > $this->maxMemoryUsage) {
            throw SimulationException::resourceExhaustion(
                'memory',
                [
                    'current_usage' => $memoryUsage,
                    'max_allowed' => $this->maxMemoryUsage,
                ]
            );
        }
    }

    /**
     * Sort command queue by priority.
     */
    protected function sortQueueByPriority(): void
    {
        usort($this->commandQueue, function ($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
    }

    /**
     * Record command execution.
     */
    protected function recordExecution(SimulationCommand $command, WorldState $world, CharacterState $character, float $startTime): void
    {
        $executionTime = microtime(true) - $startTime;
        
        $this->executedCommands[] = [
            'command' => $command,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true),
            'world_state_hash' => $this->hashWorldState($world),
            'character_state_hash' => $this->hashCharacterState($character),
        ];
    }

    /**
     * Handle command execution errors.
     */
    protected function handleCommandError(SimulationCommand $command, \Exception $e, WorldState $world, CharacterState $character): void
    {
        \Log::error('Command execution failed', [
            'command' => $command->getDescription(),
            'command_type' => $command->getType(),
            'error' => $e->getMessage(),
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
            ],
            'timestamp' => microtime(true),
        ]);

        throw new SimulationException(
            "Command execution failed: " . $e->getMessage(),
            0,
            $e
        );
    }

    /**
     * Check if execution should stop on error.
     */
    protected function shouldStopOnError(\Exception $e): bool
    {
        // Stop on critical errors
        if ($e instanceof SimulationException) {
            return true;
        }
        
        // Continue on non-critical errors
        return false;
    }

    /**
     * Rollback a failed transaction.
     */
    protected function rollbackTransaction(array $executedCommands, WorldState $originalWorld, CharacterState $originalCharacter): void
    {
        // Restore original states
        // Note: This is a simplified rollback - in practice, you'd need proper state cloning
        
        foreach (array_reverse($executedCommands) as $commandData) {
            $command = $commandData['command'] ?? $commandData;
            
            if ($command->canUndo()) {
                try {
                    $command->undo($originalWorld, $originalCharacter);
                } catch (\Exception $e) {
                    \Log::error('Failed to rollback command', [
                        'command' => $command->getDescription(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Calculate total execution cost.
     */
    protected function calculateTotalExecutionCost(): int
    {
        $totalCost = 0;
        
        foreach ($this->executedCommands as $execution) {
            $command = $execution['command'] ?? $execution;
            $totalCost += $command->getExecutionCost();
        }
        
        return $totalCost;
    }

    /**
     * Create hash of world state for change detection.
     */
    protected function hashWorldState(WorldState $world): string
    {
        return md5(json_encode([
            'public_awareness' => $world->publicAwareness,
            'power_centers' => $world->powerCenters,
            'tier_index' => $world->tierIndex,
            'faction_count' => count($world->factions),
        ]));
    }

    /**
     * Create hash of character state for change detection.
     */
    protected function hashCharacterState(CharacterState $character): string
    {
        return md5(json_encode([
            // Add character state properties as needed
        ]));
    }

    /**
     * Set maximum queue size.
     */
    public function setMaxQueueSize(int $size): void
    {
        $this->maxQueueSize = $size;
    }

    /**
     * Set maximum execution time.
     */
    public function setMaxExecutionTime(float $seconds): void
    {
        $this->maxExecutionTime = $seconds;
    }

    /**
     * Set maximum memory usage.
     */
    public function setMaxMemoryUsage(int $bytes): void
    {
        $this->maxMemoryUsage = $bytes;
    }
}
