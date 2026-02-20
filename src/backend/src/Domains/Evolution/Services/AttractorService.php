<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;

/**
 * AttractorService
 * 
 * Manages the "pull" forces toward narrative basins (Stability, War, Collapse, etc.)
 */
class AttractorService
{
    public const BASIN_STABILITY = 'STABILITY';
    public const BASIN_WAR = 'WAR';
    public const BASIN_COLLAPSE = 'COLLAPSE';
    public const BASIN_RENAISSANCE = 'RENAISSANCE';

    private array $centroids;

    public function __construct()
    {
        $this->centroids = [
            self::BASIN_STABILITY => [
                'stability' => 0.8, 'prosperity' => 0.7, 'legitimacy' => 0.8, 
                'inequality' => 0.2, 'militaryPressure' => 0.2
            ],
            self::BASIN_WAR => [
                'stability' => 0.4, 'prosperity' => 0.3, 'legitimacy' => 0.4, 
                'inequality' => 0.5, 'militaryPressure' => 0.85
            ],
            self::BASIN_COLLAPSE => [
                'stability' => 0.1, 'prosperity' => 0.1, 'legitimacy' => 0.1, 
                'inequality' => 0.8, 'militaryPressure' => 0.5
            ],
            self::BASIN_RENAISSANCE => [
                'stability' => 0.6, 'prosperity' => 0.8, 'legitimacy' => 0.7, 
                'inequality' => 0.3, 'militaryPressure' => 0.2
            ],
        ];
    }

    public function classify(CivilizationSnapshot $state): string
    {
        // 1. Extreme Critical Conditions (Hard Rules)
        if ($state->internalEntropy > 0.9 && $state->stability < 0.2) {
            return self::BASIN_COLLAPSE;
        }

        if ($state->militaryPressure > 0.8 && $state->stability < 0.5) {
            return self::BASIN_WAR;
        }

        if ($state->prosperity > 0.7 && $state->stability > 0.7 && $state->internalEntropy < 0.3) {
            return self::BASIN_RENAISSANCE;
        }

        // 2. Default to Distance-based classification if no hard rule applies
        $minDist = INF;
        $closest = self::BASIN_STABILITY;

        foreach ($this->centroids as $name => $centroid) {
            $dist = 0.0;
            foreach ($centroid as $dim => $goal) {
                // Use the dim mapping for properties
                $val = $this->getPropertyValue($state, $dim);
                $dist += ($val - $goal) ** 2;
            }
            if ($dist < $minDist) {
                $minDist = $dist;
                $closest = $name;
            }
        }

        return $closest;
    }

    private function getPropertyValue(CivilizationSnapshot $state, string $dim): float
    {
        return match($dim) {
            'stability' => $state->stability,
            'prosperity' => $state->prosperity,
            'legitimacy' => $state->legitimacy,
            'inequality' => $state->inequality,
            'militaryPressure' => $state->militaryPressure,
            default => 0.0
        };
    }

    /**
     * Calculate the "pull force" toward the dominant attractor.
     */
    public function calculatePull(CivilizationSnapshot $state, string $basin): array
    {
        $pull = [];
        $target = $this->centroids[$basin] ?? $this->centroids[self::BASIN_STABILITY];

        foreach ($target as $dim => $goal) {
            // Push state toward goal with strength based on distance
            $pull[$dim] = ($goal - $state->$dim) * 0.05;
        }

        return $pull;
    }
}
