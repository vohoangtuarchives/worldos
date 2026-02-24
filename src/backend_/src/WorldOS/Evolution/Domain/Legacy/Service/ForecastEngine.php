<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\RiskForecast;

/**
 * ForecastEngine - The simulated Meta-AI Layer used by the civilization to predict risks.
 */
final class ForecastEngine
{
    private const IRREDUCIBLE_UNCERTAINTY = 0.05;

    public function predict(CivilizationSnapshot $state, int $horizon, float $aii): RiskForecast
    {
        // Calculate true hidden risks based on current state parameters
        $trueCollapseProbability = min(1.0, $state->internalEntropy * 0.4 + $state->inequality * 0.4 + $state->structuralEntropy * 0.2);
        
        // Exogenous shock complexity (assuming higher tech and expansion implies higher complex shock surfaces)
        $shockComplexity = min(1.0, $state->technologicalLevel * 0.2 + $state->expansionism * 0.3);
        
        // Prediction Error calculation: ShockComplexity / AII
        // If AII is high, prediction error is low (but bounded by irreducible uncertainty)
        // Ensure AII is not zero
        $safeAii = max(0.01, $aii);
        $baseError = ($shockComplexity / $safeAii) * 0.1;
        
        $predictionError = max(self::IRREDUCIBLE_UNCERTAINTY, $baseError);

        // Simulated noise: A civilization might predict 0.2 but true risk is 0.5
        // Standard normal estimation
        $predictedCollapse = $this->addNoise($trueCollapseProbability, $predictionError);
        
        $predictedEntropyTraj = $this->addNoise($state->internalEntropy + 0.05, $predictionError);
        $predictedReformSuccess = $this->addNoise(max(0.0, 1.0 - $state->structuralEntropy), $predictionError);

        return new RiskForecast(
            collapseProbability: max(0.0, min(1.0, $predictedCollapse)),
            entropyTrajectory: max(0.0, min(1.0, $predictedEntropyTraj)),
            shockRiskVector: [
                'social_unrest' => max(0.0, min(1.0, $this->addNoise($state->inequality, $predictionError))),
                'ecological_collapse' => max(0.0, min(1.0, $this->addNoise($state->prosperity * 0.5, $predictionError))),
                'external_invasion' => max(0.0, min(1.0, $this->addNoise($state->externalThreat, $predictionError)))
            ],
            reformSuccessProbability: max(0.0, min(1.0, $predictedReformSuccess)),
            predictionError: $predictionError
        );
    }

    private function addNoise(float $trueValue, float $errorMargin): float
    {
        // Simple uniform noise within [-errorMargin, +errorMargin]
        $noise = (mt_rand() / mt_getrandmax() * 2 - 1) * $errorMargin;
        return $trueValue + $noise;
    }
}
