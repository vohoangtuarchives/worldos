<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

use InvalidArgumentException;

/**
 * EnvironmentState - Represents the meso-scale environment of a region/world.
 * Sits between Cosmic and Civilization; receives pressure from CosmicState, has tipping points and memory.
 */
final class EnvironmentState
{
    public function __construct(
        public readonly float $leyEnergy,
        public readonly float $terrainStability,
        public readonly float $biosphereVitality,
        public readonly float $anomalyDensity,
        public readonly int $year,
    ) {
        $this->validate();
    }

    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            leyEnergy: 0.50,
            terrainStability: 0.85,
            biosphereVitality: 0.75,
            anomalyDensity: 0.05,
            year: $year,
        );
    }

    public function evolve(CosmicState $cosmicState, float $civilizationImpact = 0.0, int $deltaYears = 100): self
    {
        $dt = $deltaYears / 100.0;
        $inertia = 0.05;
        $newLeyEnergy = $this->leyEnergy + $inertia * ($cosmicState->energy - $this->leyEnergy) * $dt;
        $newLeyEnergy += $civilizationImpact * 0.02 * $dt;
        $newLeyEnergy = $this->clamp($newLeyEnergy, 0.0, 1.0);

        $newTerrainStability = $this->terrainStability
            - $cosmicState->strain * 0.02 * $dt
            + 0.005 * $dt;
        $newTerrainStability = $this->clamp($newTerrainStability, 0.0, 1.0);

        $newBiosphereVitality = $this->biosphereVitality
            + ($newLeyEnergy - 0.5) * 0.01 * $dt
            + ($newTerrainStability - 0.5) * 0.01 * $dt
            - $civilizationImpact * 0.01 * $dt;
        $newBiosphereVitality = $this->clamp($newBiosphereVitality, 0.0, 1.0);

        $anomalyGrowth = $cosmicState->entropy * (1.0 - $newTerrainStability) * 0.03 * $dt;
        $anomalyDecay = $newBiosphereVitality * 0.01 * $dt;
        $newAnomalyDensity = $this->anomalyDensity + $anomalyGrowth - $anomalyDecay;

        if ($newAnomalyDensity > 0.7) {
            $newTerrainStability *= 0.95;
            $newBiosphereVitality *= 0.90;
            $newAnomalyDensity *= 0.6;
        }
        $newAnomalyDensity = $this->clamp($newAnomalyDensity, 0.0, 1.0);

        return new self(
            leyEnergy: round($newLeyEnergy, 6),
            terrainStability: round($newTerrainStability, 6),
            biosphereVitality: round($newBiosphereVitality, 6),
            anomalyDensity: round($newAnomalyDensity, 6),
            year: $this->year + $deltaYears,
        );
    }

    public function cosmicSignalMultiplier(): float
    {
        $instability = 1.0 - $this->terrainStability;
        return 1.0 + ($instability * 0.3) + ($this->anomalyDensity * 0.5);
    }

    public function environmentalPressure(): float
    {
        return (1.0 - $this->terrainStability) * 0.3
             + (1.0 - $this->biosphereVitality) * 0.3
             + $this->anomalyDensity * 0.4;
    }

    public function toArray(): array
    {
        return [
            'ley_energy' => $this->leyEnergy,
            'terrain_stability' => $this->terrainStability,
            'biosphere_vitality' => $this->biosphereVitality,
            'anomaly_density' => $this->anomalyDensity,
            'year' => $this->year,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            leyEnergy: (float) $data['ley_energy'],
            terrainStability: (float) $data['terrain_stability'],
            biosphereVitality: (float) $data['biosphere_vitality'],
            anomalyDensity: (float) $data['anomaly_density'],
            year: (int) $data['year'],
        );
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function validate(): void
    {
        foreach (['leyEnergy', 'terrainStability', 'biosphereVitality', 'anomalyDensity'] as $prop) {
            if ($this->$prop < 0.0 || $this->$prop > 1.0) {
                throw new InvalidArgumentException("{$prop} must be between 0.0 and 1.0, got {$this->$prop}");
            }
        }
    }
}
