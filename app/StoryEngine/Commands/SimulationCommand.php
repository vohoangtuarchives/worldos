<?php

namespace App\StoryEngine\Commands;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;

abstract class SimulationCommand
{
    protected string $description;
    protected array $metadata;
    protected float $timestamp;

    public function __construct(string $description = '', array $metadata = [])
    {
        $this->description = $description;
        $this->metadata = $metadata;
        $this->timestamp = microtime(true);
    }

    /**
     * Execute the command.
     */
    abstract public function execute(WorldState $world, CharacterState $character): void;

    /**
     * Validate if the command can be executed.
     */
    abstract public function validate(WorldState $world, CharacterState $character): bool;

    /**
     * Get the command description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get command metadata.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get command timestamp.
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * Get command type for logging and categorization.
     */
    abstract public function getType(): string;

    /**
     * Get the estimated execution cost (for resource management).
     */
    abstract public function getExecutionCost(): int;

    /**
     * Check if command can be undone.
     */
    public function canUndo(): bool
    {
        return false;
    }

    /**
     * Undo the command (if supported).
     */
    public function undo(WorldState $world, CharacterState $character): void
    {
        throw new \Exception('Command cannot be undone');
    }

    /**
     * Get the inverse command (if supported).
     */
    public function getInverse(): ?self
    {
        return null;
    }

    /**
     * Serialize command for storage/transmission.
     */
    public function serialize(): array
    {
        return [
            'type' => static::class,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Create command from serialized data.
     */
    public static function deserialize(array $data): self
    {
        // Implementation should be provided by concrete classes
        throw new \Exception('Deserialization not implemented for this command type');
    }

    /**
     * Get command hash for deduplication.
     */
    public function getHash(): string
    {
        return md5(static::class . serialize($this->metadata) . $this->timestamp);
    }

    /**
     * Check if this command conflicts with another command.
     */
    public function conflictsWith(self $other): bool
    {
        return false; // Default: no conflicts
    }

    /**
     * Get resource requirements for execution.
     */
    public function getResourceRequirements(): array
    {
        return [
            'memory' => 1024, // 1KB default
            'time' => 0.1, // 0.1 seconds default
            'io' => false,
        ];
    }

    /**
     * Check if command is idempotent.
     */
    public function isIdempotent(): bool
    {
        return false;
    }

    /**
     * Get command priority for execution ordering.
     */
    public function getPriority(): int
    {
        return 0; // Normal priority
    }

    /**
     * Get execution context requirements.
     */
    public function getExecutionContextRequirements(): array
    {
        return [
            'world_state' => true,
            'character_state' => true,
            'faction_states' => false,
            'seed_data' => false,
        ];
    }
}
