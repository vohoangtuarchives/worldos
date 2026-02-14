<?php

namespace App\StoryEngine\Commands;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\Seed;
use App\Domains\World\Services\WorldLawValidator;
use App\Domains\World\ValueObjects\WorldLawProfile;
use App\Exceptions\World\WorldLawViolationException;

class ApplySeedCommand extends SimulationCommand
{
    private Seed $seed;
    private ?WorldLawProfile $worldLawProfile;
    private ?WorldLawValidator $validator;

    public function __construct(
        Seed $seed,
        ?WorldLawProfile $worldLawProfile = null,
        ?WorldLawValidator $validator = null,
        string $description = ''
    ) {
        $this->seed = $seed;
        $this->worldLawProfile = $worldLawProfile;
        $this->validator = $validator;
        
        $description = $description ?: "Apply seed: {$seed->type} ({$seed->dimension}) with severity {$seed->severity}";
        
        parent::__construct($description, [
            'seed_type' => $seed->type,
            'seed_dimension' => $seed->dimension,
            'seed_severity' => $seed->severity,
        ]);
    }

    public function execute(WorldState $world, CharacterState $character): void
    {
        // Validate against world laws if profile is available
        if ($this->worldLawProfile && $this->validator) {
            $this->validateWorldLaws();
        }

        // Apply seed effects to world state
        $this->applySeedToWorld($world);
        
        // Apply seed effects to character state
        $this->applySeedToCharacter($character);
        
        // Record the application for potential undo
        $this->recordApplication($world, $character);
    }

    public function validate(WorldState $world, CharacterState $character): bool
    {
        // Check if seed is valid
        if (!$this->isValidSeed()) {
            return false;
        }

        // Check world state constraints
        if (!$this->checkWorldConstraints($world)) {
            return false;
        }

        // Check character state constraints
        if (!$this->checkCharacterConstraints($character)) {
            return false;
        }

        // Validate against world laws
        if ($this->worldLawProfile && $this->validator) {
            return $this->validateWorldLawsSilently();
        }

        return true;
    }

    public function getType(): string
    {
        return 'apply_seed';
    }

    public function getExecutionCost(): int
    {
        // Cost based on seed severity and type
        $baseCost = $this->seed->severity * 10;
        
        // Additional cost for complex seed types
        $typeMultipliers = [
            'POWER_GAP' => 1.5,
            'CONFLICT' => 2.0,
            'MYSTERY' => 1.2,
            'OPPORTUNITY' => 1.0,
            'CRISIS' => 2.5,
        ];
        
        $multiplier = $typeMultipliers[$this->seed->type] ?? 1.0;
        
        return (int)($baseCost * $multiplier);
    }

    public function canUndo(): bool
    {
        return true; // Seed applications can be undone
    }

    public function undo(WorldState $world, CharacterState $character): void
    {
        // Reverse the seed effects
        $this->reverseSeedEffects($world, $character);
        
        // Record the undo
        $this->recordUndo($world, $character);
    }

    public function getInverse(): ?self
    {
        // Create a command that reverses this seed application
        return new RemoveSeedCommand($this->seed, $this->description . ' (UNDO)');
    }

    public static function deserialize(array $data): self
    {
        $seed = new Seed($data['metadata']['seed_type'], $data['metadata']['seed_dimension'], $data['metadata']['seed_severity']);
        
        return new self(
            $seed,
            null, // World law profile not serialized
            null, // Validator not serialized
            $data['description']
        );
    }

    public function conflictsWith(SimulationCommand $other): bool
    {
        if ($other instanceof ApplySeedCommand) {
            // Conflicts if applying the same seed type to the same dimension
            return $this->seed->type === $other->seed->type 
                && $this->seed->dimension === $other->seed->dimension;
        }
        
        if ($other instanceof RemoveSeedCommand) {
            // Conflicts if removing the same seed being applied
            return $this->seed->type === $other->getSeed()->type 
                && $this->seed->dimension === $other->getSeed()->dimension;
        }
        
        return false;
    }

    public function isIdempotent(): bool
    {
        // Applying the same seed multiple times may have cumulative effects
        return false;
    }

    public function getPriority(): int
    {
        // Higher priority for more severe seeds
        return $this->seed->severity;
    }

    /**
     * Get the seed being applied.
     */
    public function getSeed(): Seed
    {
        return $this->seed;
    }

    /**
     * Validate the seed itself.
     */
    protected function isValidSeed(): bool
    {
        return !empty($this->seed->type) 
            && !empty($this->seed->dimension) 
            && $this->seed->severity >= 1 
            && $this->seed->severity <= 10;
    }

    /**
     * Check world state constraints.
     */
    protected function checkWorldConstraints(WorldState $world): bool
    {
        // Check if world can accept this seed type
        switch ($this->seed->type) {
            case 'POWER_GAP':
                return $world->powerCenters > 0;
                
            case 'CRISIS':
                return $world->publicAwareness > 3; // Crisis needs some awareness
                
            case 'OPPORTUNITY':
                return $world->publicAwareness < 8; // Opportunities work better in lower awareness
                
            default:
                return true;
        }
    }

    /**
     * Check character state constraints.
     */
    protected function checkCharacterConstraints(CharacterState $character): bool
    {
        // Most seed applications don't have character constraints
        return true;
    }

    /**
     * Validate against world laws silently.
     */
    protected function validateWorldLawsSilently(): bool
    {
        try {
            $this->validateWorldLaws();
            return true;
        } catch (WorldLawViolationException $e) {
            return false;
        }
    }

    /**
     * Validate against world laws.
     */
    protected function validateWorldLaws(): void
    {
        if (!$this->worldLawProfile || !$this->validator) {
            return;
        }

        $validatedSeed = $this->validator->validateSeedApplication($this->worldLawProfile, $this->seed);
        
        if ($validatedSeed === null) {
            throw WorldLawViolationException::magicViolation(
                "Seed {$this->seed->type} violates world laws",
                ['seed' => $this->seed->toArray()]
            );
        }
        
        // Update seed with validated version (may have clamped severity)
        $this->seed = $validatedSeed;
    }

    /**
     * Apply seed effects to world state.
     */
    protected function applySeedToWorld(WorldState $world): void
    {
        switch ($this->seed->type) {
            case 'POWER_GAP':
                $world->powerCenters = max(1, $world->powerCenters - 1);
                break;
                
            case 'CRISIS':
                $world->publicAwareness = min(10, $world->publicAwareness + $this->seed->severity);
                break;
                
            case 'OPPORTUNITY':
                $world->publicAwareness = max(0, $world->publicAwareness - 1);
                break;
                
            case 'MYSTERY':
                // Mystery affects awareness based on severity
                $awarenessChange = $this->seed->severity > 5 ? 1 : -1;
                $world->publicAwareness = max(0, min(10, $world->publicAwareness + $awarenessChange));
                break;
        }
    }

    /**
     * Apply seed effects to character state.
     */
    protected function applySeedToCharacter(CharacterState $character): void
    {
        // Most seeds don't directly affect character state
        // This can be extended based on specific seed types
    }

    /**
     * Record the seed application.
     */
    protected function recordApplication(WorldState $world, CharacterState $character): void
    {
        \Log::info('Seed applied', [
            'command' => $this->getDescription(),
            'seed_type' => $this->seed->type,
            'seed_dimension' => $this->seed->dimension,
            'seed_severity' => $this->seed->severity,
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
            ],
            'timestamp' => $this->timestamp,
        ]);
    }

    /**
     * Reverse seed effects.
     */
    protected function reverseSeedEffects(WorldState $world, CharacterState $character): void
    {
        // Reverse the world state changes
        switch ($this->seed->type) {
            case 'POWER_GAP':
                $world->powerCenters += 1;
                break;
                
            case 'CRISIS':
                $world->publicAwareness = max(0, $world->publicAwareness - $this->seed->severity);
                break;
                
            case 'OPPORTUNITY':
                $world->publicAwareness += 1;
                break;
                
            case 'MYSTERY':
                $awarenessChange = $this->seed->severity > 5 ? 1 : -1;
                $world->publicAwareness = max(0, min(10, $world->publicAwareness - $awarenessChange));
                break;
        }
    }

    /**
     * Record the undo operation.
     */
    protected function recordUndo(WorldState $world, CharacterState $character): void
    {
        \Log::info('Seed application undone', [
            'command' => $this->getDescription(),
            'seed_type' => $this->seed->type,
            'seed_dimension' => $this->seed->dimension,
            'seed_severity' => $this->seed->severity,
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
            ],
            'timestamp' => microtime(true),
        ]);
    }
}
