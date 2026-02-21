<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

/**
 * RiskForecast - The output of the ForecastEngine (Meta-AI Layer).
 */
final class RiskForecast
{
    public function __construct(
        public readonly float $collapseProbability,
        public readonly float $entropyTrajectory,
        public readonly array $shockRiskVector,     // e.g. ['social' => 0.2, 'ecological' => 0.4, 'external' => 0.1]
        public readonly float $reformSuccessProbability,
        public readonly float $predictionError      // The calculated error margin based on AII and Uncertainty
    ) {
    }

    public function toArray(): array
    {
        return [
            'collapse_probability' => $this->collapseProbability,
            'entropy_trajectory' => $this->entropyTrajectory,
            'shock_risk_vector' => $this->shockRiskVector,
            'reform_success_probability' => $this->reformSuccessProbability,
            'prediction_error' => $this->predictionError,
        ];
    }
}
