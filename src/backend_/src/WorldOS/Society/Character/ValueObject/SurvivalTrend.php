<?php

declare(strict_types=1);

namespace WorldOS\Society\Character\ValueObject;

/**
 * Trend of survival probabilities over time for a character.
 */
readonly class SurvivalTrend
{
    /** @param list<float> $probabilities */
    public function __construct(
        public string $characterId,
        public array $probabilities,
    ) {
    }

    public function isDeclining(): bool
    {
        if (count($this->probabilities) < 2) {
            return false;
        }
        $first = $this->probabilities[0];
        $last = $this->probabilities[array_key_last($this->probabilities)];
        return $last < $first - 0.1;
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
        $recent = array_slice($this->probabilities, -$withinTicks);
        if (empty($recent)) {
            return 0.0;
        }
        $below = array_filter($recent, fn (float $p): bool => $p < 0.3);
        return count($below) / count($recent);
    }

    public function toArray(): array
    {
        return [
            'character_id' => $this->characterId,
            'probabilities' => [...$this->probabilities],
            'is_declining' => $this->isDeclining(),
            'average_probability' => $this->averageProbability(),
            'risk_of_death' => $this->riskOfDeath(),
        ];
    }
}
