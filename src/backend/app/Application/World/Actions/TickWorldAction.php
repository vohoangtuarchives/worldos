<?php

declare(strict_types=1);

namespace App\Application\World\Actions;

use App\Domains\World\Aggregates\WorldAggregate;
use App\Domains\History\Services\EntropyCalculator;
use App\Domains\World\Services\ShockEventGenerator;
use App\Domains\World\Events\ShockEvent;
use App\Domains\Character\Services\SurvivalCheckEngine;
use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\World;

/**
 * @deprecated V3: Logic moved to BasePhysicsEngine (Macro-State). Agent simulation removed for MVP.
 */
final class TickWorldAction
{
    private const ENTROPY_TICK_INCREMENT = 0.02;
    private const SHOCK_EVENT_PROBABILITY_BASE = 0.1;
    private const MAX_ENTROPY = 1.0;

    public function __construct(
        private readonly EntropyCalculator $entropyCalculator,
        private readonly ShockEventGenerator $shockGenerator,
        private readonly SurvivalCheckEngine $survivalEngine,
    ) {}

    public function execute(WorldAggregate $world, Collection $characters): TickResult
    {
        $startTime = microtime(true);
        
        try {
            DB::beginTransaction();

            $worldModel = World::find($world->id());
            if (!$worldModel) {
                throw new \Exception("World model not found for ID: " . $world->id());
            }

            // Calculate current world state
            $currentEntropyValue = $this->entropyCalculator->calculateWorldEntropy($worldModel, $world->currentTick());
            $currentEntropy = new \App\Domains\World\ValueObjects\EntropyScore($currentEntropyValue);
            
            $newTick = $world->currentTick() + 1;

            // Update entropy
            $newEntropy = $this->updateEntropy($currentEntropy, $world);

            // Generate shock events if conditions are met
            $shockEvents = $this->generateShockEvents($world, $newEntropy, $newTick);

            // Apply shock events to characters and check survival
            $survivalResults = $this->processCharacterSurvival($characters, $newEntropy, $shockEvents);

            // Update world state
            $updatedWorld = $world->advanceTick($newTick, $newEntropy, $shockEvents);

            // Record tick metrics
            $metrics = $this->calculateTickMetrics($updatedWorld, $survivalResults, $shockEvents);

            // Persist changes
            $worldModel->update([
                'current_tick' => $newTick,
                'entropy' => $newEntropy->value(),
                'last_tick_at' => now(),
            ]);

            DB::commit();

            $executionTime = microtime(true) - $startTime;

            Log::info('World tick completed', [
                'world_id' => $world->id(),
                'tick' => $newTick,
                'entropy' => $newEntropy->value(),
                'shock_events' => count($shockEvents),
                'character_deaths' => $survivalResults->filter(fn($r) => !$r->survived)->count(),
                'execution_time' => $executionTime,
            ]);

            return new TickResult(
                $updatedWorld,
                $newTick,
                $newEntropy,
                $shockEvents,
                $survivalResults,
                $metrics,
                $executionTime
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('World tick failed', [
                'world_id' => $world->id(),
                'tick' => $world->currentTick() + 1,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException("World tick failed: {$e->getMessage()}", 0, $e);
        }
    }

    private function updateEntropy(
        \App\Domains\World\ValueObjects\EntropyScore $currentEntropy,
        WorldAggregate $world
    ): \App\Domains\World\ValueObjects\EntropyScore {
        
        // Base entropy increase
        $entropyIncrease = self::ENTROPY_TICK_INCREMENT;

        // Accelerate entropy if world is unstable
        if ($currentEntropy->value() > 0.7) {
            $entropyIncrease *= 2.0;
        }

        // World-specific entropy modifiers
        $worldModifier = 1.0;
        $finalIncrease = $entropyIncrease * $worldModifier;

        $newEntropyValue = min(self::MAX_ENTROPY, $currentEntropy->value() + $finalIncrease);

        return new \App\Domains\World\ValueObjects\EntropyScore($newEntropyValue);
    }

    private function generateShockEvents(
        WorldAggregate $world,
        \App\Domains\World\ValueObjects\EntropyScore $entropy,
        int $tick
    ): Collection {
        
        $events = collect();

        // Base probability increases with entropy
        $shockProbability = self::SHOCK_EVENT_PROBABILITY_BASE * (1 + $entropy->value());

        // Check if we should generate a shock event
        if ((mt_rand() / mt_getrandmax()) < $shockProbability) {
            $event = $this->shockGenerator->generate($world, $entropy, $tick);
            $events->push($event);

            Log::info('Shock event generated', [
                'world_id' => $world->id(),
                'tick' => $tick,
                'event_type' => $event->type(),
                'severity' => $event->severity(),
            ]);
        }

        // Multiple events possible at high entropy
        if ($entropy->value() > 0.8 && random_float(0, 1) < 0.3) {
            $secondEvent = $this->shockGenerator->generate($world, $entropy, $tick);
            $events->push($secondEvent);
        }

        return $events;
    }

    private function processCharacterSurvival(
        Collection $characters,
        \App\Domains\World\ValueObjects\EntropyScore $entropy,
        Collection $shockEvents
    ): Collection {
        
        $results = collect();

        foreach ($characters as $character) {
            // Apply all shock events to character
            $affectedCharacter = $character;
            foreach ($shockEvents as $shockEvent) {
                $affectedCharacter = $affectedCharacter->applyShockEvent($shockEvent);
            }

            // Check survival with most significant shock event
            $significantShock = $shockEvents->sortByDesc(fn($e) => $e->severity())->first();
            
            $result = $this->survivalEngine->checkSurvival(
                $affectedCharacter,
                $entropy,
                $significantShock
            );

            $results->push($result);

            // Log character deaths
            if (!$result->survived) {
                Log::warning('Character died', [
                    'character_id' => $character->characterId(),
                    'tick' => $character->currentTick(),
                    'entropy' => $entropy->value(),
                    'survival_probability' => $result->probability->value(),
                    'reason' => $result->reason,
                ]);
            }
        }

        return $results;
    }

    private function calculateTickMetrics(
        WorldAggregate $world,
        Collection $survivalResults,
        Collection $shockEvents
    ): TickMetrics {
        
        $deathCount = $survivalResults->filter(fn($r) => !$r->survived)->count();
        $survivalRate = $survivalResults->isEmpty() 
            ? 1.0 
            : $survivalResults->filter(fn($r) => $r->survived)->count() / $survivalResults->count();

        $averageSurvivalProbability = $survivalResults->isEmpty()
            ? 1.0
            : $survivalResults->reduce(fn($carry, $r) => $carry + $r->probability->value(), 0) / $survivalResults->count();

        return new TickMetrics(
            deathCount: $deathCount,
            survivalRate: $survivalRate,
            averageSurvivalProbability: $averageSurvivalProbability,
            shockEventCount: $shockEvents->count(),
            worldStability: $this->calculateWorldStability($world, $survivalResults),
        );
    }

    private function calculateWorldStability(
        WorldAggregate $world,
        Collection $survivalResults
    ): float {
        
        $entropy = $world->currentEntropy();
        $deathRate = $survivalResults->isEmpty() 
            ? 0.0 
            : $survivalResults->filter(fn($r) => !$r->survived)->count() / $survivalResults->count();

        // Stability decreases with entropy and death rate
        $stability = 1.0 - ($entropy->value() * 0.6) - ($deathRate * 0.4);

        return max(0.0, min(1.0, $stability));
    }
}

final readonly class TickResult
{
    public function __construct(
        public readonly WorldAggregate $world,
        public readonly int $tick,
        public readonly \App\Domains\World\ValueObjects\EntropyScore $entropy,
        public readonly Collection $shockEvents,
        public readonly Collection $survivalResults,
        public readonly TickMetrics $metrics,
        public readonly float $executionTime,
    ) {}

    public function hasDeaths(): bool
    {
        return $this->survivalResults->filter(fn($r) => !$r->survived)->isNotEmpty();
    }

    public function getDeathCount(): int
    {
        return $this->survivalResults->filter(fn($r) => !$r->survived)->count();
    }

    public function toArray(): array
    {
        return [
            'tick' => $this->tick,
            'entropy' => $this->entropy->value(),
            'shock_events' => $this->shockEvents->map(fn($e) => $e->toArray())->toArray(),
            'survival_results' => $this->survivalResults->map(fn($r) => $r->toArray())->toArray(),
            'metrics' => $this->metrics->toArray(),
            'execution_time' => $this->executionTime,
        ];
    }
}

final readonly class TickMetrics
{
    public function __construct(
        public readonly int $deathCount,
        public readonly float $survivalRate,
        public readonly float $averageSurvivalProbability,
        public readonly int $shockEventCount,
        public readonly float $worldStability,
    ) {}

    public function toArray(): array
    {
        return [
            'death_count' => $this->deathCount,
            'survival_rate' => $this->survivalRate,
            'average_survival_probability' => $this->averageSurvivalProbability,
            'shock_event_count' => $this->shockEventCount,
            'world_stability' => $this->worldStability,
        ];
    }
}
