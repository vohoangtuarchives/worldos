<?php

declare(strict_types=1);

namespace App\Domains\Character\Services;

use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use App\Domains\Character\Events\CharacterDeathEvent;
use App\Domains\Character\Events\CharacterSurvivedEvent;
use App\Domains\Character\ValueObjects\SurvivalProbability;
use App\Domains\World\ValueObjects\EntropyScore;
use App\Domains\World\Events\ShockEvent;
use Illuminate\Support\Collection;

final class SurvivalCheckEngine
{
    private const SURVIVAL_THRESHOLD = 0.3;
    private const DEATH_PROBABILITY_FACTOR = 0.7;

    public function checkSurvival(
        CharacterSurvivalAggregate $character,
        EntropyScore $worldEntropy,
        ?ShockEvent $shockEvent = null
    ): SurvivalResult {
        
        // Apply shock event if present
        $affectedCharacter = $shockEvent 
            ? $character->applyShockEvent($shockEvent)
            : $character;

        // Calculate survival probability
        $survivalProbability = $affectedCharacter->calculateSurvivalProbability($worldEntropy);

        // Check if character can die under current conditions
        if (!$affectedCharacter->canDie($worldEntropy)) {
            return SurvivalResult::survived($affectedCharacter, $survivalProbability, 'Protected by narrative rules');
        }

        // Determine outcome
        if ($this->shouldDie($survivalProbability, $worldEntropy, $shockEvent)) {
            return SurvivalResult::died($affectedCharacter, $survivalProbability, 'World conditions led to death');
        }

        return SurvivalResult::survived($affectedCharacter, $survivalProbability, 'Survived against odds');
    }

    public function checkMultipleSurvival(
        Collection $characters,
        EntropyScore $worldEntropy,
        ?ShockEvent $shockEvent = null
    ): Collection {
        
        return $characters->map(function (CharacterSurvivalAggregate $character) use ($worldEntropy, $shockEvent) {
            return $this->checkSurvival($character, $worldEntropy, $shockEvent);
        });
    }

    public function predictSurvivalTrend(
        CharacterSurvivalAggregate $character,
        EntropyScore $currentEntropy,
        int $futureTicks = 5
    ): SurvivalTrend {
        
        $probabilities = [];
        $entropy = $currentEntropy;

        for ($i = 1; $i <= $futureTicks; $i++) {
            // Simulate entropy increase
            $entropy = new EntropyScore(min(1.0, $entropy->value() + 0.05));
            
            $probability = $character->calculateSurvivalProbability($entropy);
            $probabilities[] = $probability->value();
        }

        return new SurvivalTrend($character->characterId(), $probabilities);
    }

    private function shouldDie(
        SurvivalProbability $probability,
        EntropyScore $worldEntropy,
        ?ShockEvent $shockEvent
    ): bool {
        
        // Base death check
        if ($probability->value() < self::SURVIVAL_THRESHOLD) {
            return true;
        }

        // Shock event increases death probability
        if ($shockEvent && $shockEvent->isCatastrophic()) {
            $adjustedProbability = $probability->multiply(self::DEATH_PROBABILITY_FACTOR);
            return $adjustedProbability->value() < self::SURVIVAL_THRESHOLD;
        }

        // High entropy makes death more likely
        if ($worldEntropy->value() > 0.8) {
            $entropyPenalty = ($worldEntropy->value() - 0.8) * 2;
            $adjustedProbability = $probability->adjust(-$entropyPenalty);
            return $adjustedProbability->value() < self::SURVIVAL_THRESHOLD;
        }

        return false;
    }

    public function calculateGroupSurvivalRate(
        Collection $characters,
        EntropyScore $worldEntropy
    ): float {
        
        if ($characters->isEmpty()) {
            return 0.0;
        }

        $totalProbability = $characters->reduce(function (float $carry, CharacterSurvivalAggregate $character) use ($worldEntropy) {
            $probability = $character->calculateSurvivalProbability($worldEntropy);
            return $carry + $probability->value();
        }, 0.0);

        return $totalProbability / $characters->count();
    }

    public function identifyHighRiskCharacters(
        Collection $characters,
        EntropyScore $worldEntropy,
        float $riskThreshold = 0.4
    ): Collection {
        
        return $characters->filter(function (CharacterSurvivalAggregate $character) use ($worldEntropy, $riskThreshold) {
            $probability = $character->calculateSurvivalProbability($worldEntropy);
            return $probability->value() < $riskThreshold;
        });
    }
}

final readonly class SurvivalResult
{
    private function __construct(
        public readonly CharacterSurvivalAggregate $character,
        public readonly SurvivalProbability $probability,
        public readonly bool $survived,
        public readonly string $reason,
    ) {}

    public static function survived(CharacterSurvivalAggregate $character, SurvivalProbability $probability, string $reason): self
    {
        return new self($character, $probability, true, $reason);
    }

    public static function died(CharacterSurvivalAggregate $character, SurvivalProbability $probability, string $reason): self
    {
        return new self($character->markAsDead(), $probability, false, $reason);
    }

    public function toArray(): array
    {
        return [
            'character_id' => $this->character->characterId(),
            'survived' => $this->survived,
            'probability' => $this->probability->value(),
            'reason' => $this->reason,
        ];
    }
}

final readonly class SurvivalTrend
{
    private function __construct(
        public readonly string $characterId,
        public readonly array $probabilities,
    ) {}

    public function isDeclining(): bool
    {
        if (count($this->probabilities) < 2) {
            return false;
        }

        $first = $this->probabilities[0];
        $last = end($this->probabilities);

        return $last < $first - 0.1; // Decline by more than 10%
    }

    public function averageProbability(): float
    {
        if (empty($this->probabilities)) {
            return 0.0;
        }

        return array_sum($this->probabilities) / count($this->probabilities);
    }

    public function riskOfDeath(int $withinTicks = 3): float
    {
        $recentProbabilities = array_slice($this->probabilities, -$withinTicks);
        
        if (empty($recentProbabilities)) {
            return 0.0;
        }

        $belowThreshold = array_filter($recentProbabilities, fn($p) => $p < 0.3);
        
        return count($belowThreshold) / count($recentProbabilities);
    }

    public function toArray(): array
    {
        return [
            'character_id' => $this->characterId,
            'probabilities' => $this->probabilities,
            'is_declining' => $this->isDeclining(),
            'average_probability' => $this->averageProbability(),
            'risk_of_death' => $this->riskOfDeath(),
        ];
    }
}
