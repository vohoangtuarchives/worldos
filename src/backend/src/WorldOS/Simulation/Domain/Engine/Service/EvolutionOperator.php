<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\Service;

use WorldOS\Kernel\Domain\Policy\CompiledPolicy;
use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;
use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;

/**
 * EvolutionOperator: The deterministic heart of the simulation tick engine.
 *
 * Algorithm per tick:
 *   1. A · S(t)   — cross-dimensional coupling (matrix multiply)
 *   2. + N(t)     — seeded Gaussian noise injection
 *   3. Clamp      — bound all values to [0.0, 1.0]
 *   4. Weight     — compute existence weight via CompiledPolicy formula
 *   5. Anomalies  — detect threshold breaches
 *
 * Determinism guarantee: given the same (seed, tick, StateVector),
 * the output TickResult is always identical.
 *
 * Cross-dimension coupling is now fully modelled: entropy at t affects
 * stability at t+1, stability affects power_density, etc. — as defined
 * by the 17×17 CouplingMatrix.
 */
final class EvolutionOperator
{
    public function __construct(
        private readonly CompiledPolicy $policy,
        private readonly CouplingMatrix $couplingMatrix,
    ) {}

    /**
     * Evolve a StateVector by one tick.
     * S(t+1) = clamp( A·S(t) + N(t) )
     *
     * @param array<string,float> $criticalThresholds Keyed by dimension name
     */
    public function evolve(
        StateVector $current,
        int         $tick,
        int         $seed,
        array       $criticalThresholds = []
    ): TickResult {
        mt_srand($seed ^ $tick); // Seeded RNG — determinism per tick

        $prevEntropy = $current->get(StateVector::DIMENSION_ENTROPY);

        // 1. A · S(t) — cross-dimensional linear coupling
        $coupled = $this->couplingMatrix->multiply($current);

        // 2. + N(t) — seeded Gaussian noise per dimension
        $noiseVector = $this->buildNoiseVector($current, $this->policy->getChaosFactor());
        $raw         = $coupled->add($noiseVector);

        // 3. Clamp to valid domain [0.0, 1.0]
        $next = $raw->clamp(0.0, 1.0);

        // 4. Entropy delta
        $entropyDelta = $next->get(StateVector::DIMENSION_ENTROPY) - $prevEntropy;

        // 5. Existence weight via compiled policy formula
        $weight = $this->policy->evaluateWeight([
            'w'             => $next->get(StateVector::DIMENSION_STABILITY),
            'anomaly'       => $next->get(StateVector::DIMENSION_ANOMALY),
            'richness'      => $next->get(StateVector::DIMENSION_RICHNESS),
            'entropy_decay' => max(0.0, $entropyDelta),
        ]);

        // 6. Detect anomalies
        $anomalies = $this->detectAnomalies($next, $criticalThresholds);

        return new TickResult(
            tick:            $tick,
            seed:            $seed,
            nextStateVector: $next,
            entropyDelta:    $entropyDelta,
            existenceWeight: $weight,
            anomalies:       $anomalies
        );
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Build a noise vector N(t): one Gaussian-distributed value per dimension.
     * Uses seeded mt_rand (Box-Muller) — fully deterministic.
     */
    private function buildNoiseVector(StateVector $template, float $scale): StateVector
    {
        $dims = [];
        foreach (array_keys($template->all()) as $key) {
            $dims[$key] = $this->seededGaussian($scale);
        }
        return StateVector::fromArray($dims);
    }

    /**
     * Box-Muller Gaussian approximation scaled by $scale.
     * mt_rand is already seeded at the top of evolve().
     */
    private function seededGaussian(float $scale): float
    {
        $u1 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        $u2 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;

        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2) * $scale;
    }

    /**
     * Detect dimension breaches against per-dimension critical thresholds.
     *
     * @return AnomalyEvent[]
     */
    private function detectAnomalies(StateVector $state, array $criticalThresholds): array
    {
        $anomalies = [];
        foreach ($criticalThresholds as $dimension => $threshold) {
            $value = $state->get($dimension);
            if ($value >= $threshold) {
                $intensity   = min(1.0, ($value - $threshold) / (1.0 - $threshold + 1e-9));
                $anomalies[] = new AnomalyEvent($dimension, $value, $threshold, $intensity);
            }
        }
        return $anomalies;
    }
}
