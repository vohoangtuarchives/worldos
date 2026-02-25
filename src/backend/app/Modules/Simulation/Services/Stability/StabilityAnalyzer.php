<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Stability;

use App\Modules\WorldTemplate\Contracts\StabilityAnalyzerInterface;
use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\StabilityMetric;
use App\Modules\Shared\ValueObjects\WorldStateVector;

/**
 * Stability Analyzer — SimulationEngine Implementation.
 *
 * Computes σ(U) stability metric using heuristic analysis.
 * Uses 6-component weighted combination instead of expensive Jacobian eigenvalues.
 *
 * Pure computation — NO Laravel dependencies, NO side effects.
 */
final class StabilityAnalyzer implements StabilityAnalyzerInterface
{
    public function analyze(
        WorldStateVector $state,
        CascadeStateVector $cascade,
    ): StabilityMetric {
        $entropyFactor = 1.0 - $state->entropy;
        $orderFactor = $this->bellCurve($state->order, 0.5, 0.25);
        $cohesionFactor = $state->cohesion;
        $cascadeHealth = $this->calculateCascadeHealth($cascade);
        $inequalityPenalty = 1.0 - ($state->inequality * $state->inequality);
        $traumaPenalty = 1.0 - ($state->trauma * 0.8);

        $sigma = ($entropyFactor * 0.25)
            + ($orderFactor * 0.15)
            + ($cohesionFactor * 0.15)
            + ($cascadeHealth * 0.20)
            + ($inequalityPenalty * 0.15)
            + ($traumaPenalty * 0.10);

        // Critical override: extreme entropy forces collapse
        if ($state->entropy > 0.9) {
            $sigma = min($sigma, 0.1);
        }

        $sigma = max(0.0, min(1.0, $sigma));

        return new StabilityMetric($sigma);
    }

    private function calculateCascadeHealth(CascadeStateVector $cascade): float
    {
        $layers = [
            $cascade->physics,
            $cascade->chemistry,
            $cascade->biology,
            $cascade->cognition,
            $cascade->culture,
        ];

        $sum = 0.0;
        $activeCount = 0;

        foreach ($layers as $value) {
            if ($value > 0.1) {
                $sum += $value;
                $activeCount++;
            }
        }

        if ($activeCount === 0) {
            return 0.0;
        }

        $avgHealth = $sum / count($layers);

        $continuityBonus = 1.0;
        for ($i = 1; $i < count($layers); $i++) {
            if ($layers[$i] > 0.1 && $layers[$i - 1] < 0.1) {
                $continuityBonus *= 0.7;
            }
        }

        return $avgHealth * $continuityBonus;
    }

    private function bellCurve(float $value, float $center, float $spread): float
    {
        return exp(-0.5 * (($value - $center) / $spread) ** 2);
    }
}
