<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

use InvalidArgumentException;

/**
 * HeroCouplingMatrix (A_h) 8x8.
 * Defines the internal stability and feedback loops of the hero's psyche.
 * Contains purely mathematical operators. Spectral radius should be < 1.0.
 */
final class HeroCouplingMatrix
{
    /** @var array<int, array<int, float>> */
    private readonly array $matrix;

    private function __construct(array $matrix)
    {
        if (count($matrix) !== 8) {
            throw new InvalidArgumentException("HeroCouplingMatrix must have exactly 8 rows.");
        }
        foreach ($matrix as $row) {
            if (count($row) !== 8) {
                throw new InvalidArgumentException("HeroCouplingMatrix must have exactly 8 columns.");
            }
        }
        $this->matrix = $matrix;
    }

    /**
     * Default Research-Grade Matrix with 3 loops:
     * 1. Stability Loop: Stress, Conviction, Resilience (damping)
     * 2. Escalation Loop: Stress, Ego, Fear (positive feedback)
     * 3. Slow Loop: Adaptation, Clarity, Trauma (memory)
     */
    public static function createBaseline(): self
    {
        $matrix = [
            // σ,    κ,     ρ,     α,     φ,     χ,     ε,     τ
            // Row 0: Stress (σ) decays naturally, bumped by Ego/Fear, damped by Resilience
            [ 0.70, -0.20, -0.25,  0.00,  0.15,  0.00,  0.20,  0.00 ],
            // Row 1: Conviction (κ) rises slowly with stress, damped by trauma
            [ 0.25,  0.75,  0.10,  0.00,  0.00,  0.10,  0.00, -0.15 ],
            // Row 2: Resilience (ρ) grows when tested (Stress/Conviction)
            [-0.15,  0.20,  0.80,  0.05,  0.00,  0.00,  0.00,  0.00 ],
            // Row 3: Adaptation (α) grows with clarity
            [ 0.00,  0.00,  0.05,  0.85,  0.00,  0.20,  0.00, -0.10 ],
            // Row 4: Fear (φ) amplified by Stress and Ego
            [ 0.20,  0.00,  0.00,  0.00,  0.75, -0.15,  0.25,  0.00 ],
            // Row 5: Clarity (χ) boosted by conviction and adaptation, damped by trauma
            [ 0.00,  0.15,  0.00,  0.25,  0.10,  0.80,  0.00, -0.20 ],
            // Row 6: Ego (ε) amplified by Fear, and Stress
            [ 0.15,  0.00,  0.00,  0.00,  0.20,  0.00,  0.70,  0.00 ],
            // Row 7: Trauma (τ) highly persistent memory, slowly damped by Conviction/Clarity
            [ 0.00, -0.10,  0.00,  0.00,  0.00, -0.25,  0.00,  0.90 ],
        ];

        return new self($matrix);
    }

    public function multiply(HeroStateVector $vector): HeroStateVector
    {
        $vectorArr = $vector->toIndexedArray();
        $result = array_fill(0, 8, 0.0);

        for ($i = 0; $i < 8; $i++) {
            $sum = 0.0;
            for ($j = 0; $j < 8; $j++) {
                $sum += $this->matrix[$i][$j] * $vectorArr[$j];
            }
            $result[$i] = $sum;
        }

        return HeroStateVector::fromIndexedArray($result);
    }
}
