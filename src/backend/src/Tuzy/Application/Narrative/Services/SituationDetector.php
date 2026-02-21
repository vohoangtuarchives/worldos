<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Services;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Layer 1: Pure math — situations from state (and optional previous state for velocity).
 * Uses weighted scoring / logistic-style smoothing instead of hard thresholds.
 *
 * @phpstan-type Situation array{key: string, intensity: float, velocity: string, persistence: float}
 */
class SituationDetector
{
    private const VELOCITY_FAST = 0.08;
    private const VELOCITY_SLOW = 0.02;

    /**
     * @return list<Situation>
     */
    public function detect(WorldStateVector $vector, ?WorldStateVector $previousVector = null, int $cycle = 0): array
    {
        $situations = [];
        $velocity = $previousVector !== null ? $this->velocityCategory($vector, $previousVector) : [];

        $candidates = [
            ['key' => 'inequality_high', 'intensity' => $this->logistic($vector->getInequality(), 0.5, 0.25)],
            ['key' => 'trauma_high', 'intensity' => $this->logistic($vector->getTrauma(), 0.5, 0.2)],
            ['key' => 'resource_scarce', 'intensity' => $vector->getResourceStock() < 0.5 ? $this->logistic(1.0 - $vector->getResourceStock(), 0.6, 0.2) : 0.0],
            ['key' => 'resource_abundant', 'intensity' => $vector->getResourceStock() >= 0.5 ? $this->logistic($vector->getResourceStock(), 0.5, 0.2) : 0.0],
            ['key' => 'elite_corrupt', 'intensity' => ($vector->getEliteCohesion() > 0.7 && $vector->getLegitimacy() < 0.4) ? 0.9 : 0.0],
            ['key' => 'elite_fractured', 'intensity' => $vector->getEliteCohesion() < 0.4 ? $this->logistic(1.0 - $vector->getEliteCohesion(), 0.5, 0.2) : 0.0],
            ['key' => 'innovation_high', 'intensity' => $this->logistic($vector->getInnovation(), 0.6, 0.2)],
            ['key' => 'order_high_entropy_low', 'intensity' => ($vector->getOrder() > 0.6 && $vector->getEntropy() < 0.4) ? $vector->getOrder() : 0.0],
            ['key' => 'entropy_high', 'intensity' => $this->logistic($vector->getEntropy(), 0.6, 0.2)],
            ['key' => 'military_high_cohesion_low', 'intensity' => ($vector->getMilitary() > 0.6 && $vector->getCohesion() < 0.5) ? $vector->getMilitary() : 0.0],
            ['key' => 'stagnation_risk', 'intensity' => ($vector->getOrder() > 0.8 && $vector->getInnovation() < 0.25 && $vector->getEntropy() < 0.2) ? 0.8 : 0.0],
        ];

        $contradiction = $vector->getInequality() * (1.0 - $vector->getLegitimacy()) * 0.4
            + $vector->getTrauma() * 0.35 + $vector->getEntropy() * 0.25;
        $candidates[] = ['key' => 'pressure_critical', 'intensity' => $this->logistic($contradiction, 0.65, 0.15)];

        foreach ($candidates as $c) {
            if ($c['intensity'] < 0.35) {
                continue;
            }
            $vel = $velocity[$c['key']] ?? 'stable';
            $situations[] = [
                'key' => $c['key'],
                'intensity' => $c['intensity'],
                'velocity' => $vel,
                'persistence' => 0.0, // placeholder without history
            ];
        }

        if (empty($situations)) {
            $situations[] = ['key' => 'neutral', 'intensity' => 0.5, 'velocity' => 'stable', 'persistence' => 0.0];
        }

        return $situations;
    }

    /**
     * Logistic-style smoothing: 1 / (1 + exp(-k*(x - x0))). Approximate with piecewise for stability.
     */
    private function logistic(float $x, float $mid, float $steepness): float
    {
        if ($steepness <= 0) {
            return $x > $mid ? 1.0 : 0.0;
        }
        $t = ($x - $mid) / max(0.01, $steepness);
        return max(0.0, min(1.0, 1.0 / (1.0 + exp(-$t))));
    }

    /**
     * @return array<string, string> situation key => velocity category
     */
    private function velocityCategory(WorldStateVector $current, WorldStateVector $prev): array
    {
        $g = $current->gradient($prev)->getAll();
        $out = [];
        $map = [
            'inequality_high' => 'inequality',
            'trauma_high' => 'trauma',
            'resource_scarce' => 'resource_stock',
            'resource_abundant' => 'resource_stock',
            'elite_fractured' => 'elite_cohesion',
            'innovation_high' => 'innovation',
            'order_high_entropy_low' => 'order',
            'entropy_high' => 'entropy',
            'military_high_cohesion_low' => 'military',
            'pressure_critical' => null,
        ];
        foreach ($map as $sitKey => $dim) {
            if ($dim === null) {
                $out[$sitKey] = 'stable';
                continue;
            }
            $delta = $g[$dim] ?? 0.0;
            if ($delta >= self::VELOCITY_FAST) {
                $out[$sitKey] = 'rising_fast';
            } elseif ($delta >= self::VELOCITY_SLOW) {
                $out[$sitKey] = 'rising';
            } elseif ($delta <= -self::VELOCITY_FAST) {
                $out[$sitKey] = 'falling_fast';
            } elseif ($delta <= -self::VELOCITY_SLOW) {
                $out[$sitKey] = 'falling';
            } else {
                $out[$sitKey] = 'stable';
            }
        }
        return $out;
    }
}
