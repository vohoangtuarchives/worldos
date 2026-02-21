<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

use InvalidArgumentException;

/**
 * EnvironmentState - Represents the meso-scale environment of a region/world.
 *
 * This layer sits between Cosmic and Civilization.
 * - Receives pressure from CosmicState (top-down)
 * - Has its own tipping points and memory (hysteresis)
 * - Can amplify or dampen cosmic signals to civilization
 *
 * Key properties:
 * - ley_energy: Local spiritual/energy density (influenced by cosmic energy)
 * - terrain_stability: Geological/geographical stability
 * - biosphere_vitality: Health of the living ecosystem
 * - anomaly_density: Concentration of anomalous phenomena (rifts, vortices, etc.)
 */
final class EnvironmentState
{
    public function __construct(
        public readonly float $leyEnergy,         // Local energy field (0.0 to 1.0)
        public readonly float $terrainStability,   // Geological stability (0.0 to 1.0)
        public readonly float $biosphereVitality,  // Ecosystem health (0.0 to 1.0)
        public readonly float $anomalyDensity,     // Anomalous phenomena concentration (0.0 to 1.0)
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

    /**
     * Evolve environment state based on cosmic pressure.
     *
     * The environment acts as a FILTER between cosmic and civilization:
     * - It absorbs cosmic energy changes with inertia (doesn't react instantly)
     * - It has tipping points (when anomaly density crosses threshold)
     * - It has memory (terrain changes persist)
     *
     * @param CosmicState $cosmicState Current cosmic state (top-down pressure)
     * @param float $civilizationImpact How much civilization is affecting environment (0.0 to 1.0)
     * @param int $deltaYears Time step
     */
    public function evolve(CosmicState $cosmicState, float $civilizationImpact = 0.0, int $deltaYears = 100): self
    {
        $dt = $deltaYears / 100.0;

        // 1. Ley energy follows cosmic energy with inertia (slow coupling)
        $inertia = 0.05; // How fast local energy tracks cosmic energy
        $newLeyEnergy = $this->leyEnergy + $inertia * ($cosmicState->energy - $this->leyEnergy) * $dt;
        // Civilization can drain or boost local energy
        $newLeyEnergy += $civilizationImpact * 0.02 * $dt;
        $newLeyEnergy = $this->clamp($newLeyEnergy, 0.0, 1.0);

        // 2. Terrain stability is affected by cosmic strain (earthquakes, shifts)
        $newTerrainStability = $this->terrainStability
            - $cosmicState->strain * 0.02 * $dt  // Cosmic strain damages terrain
            + 0.005 * $dt;                         // Natural recovery (very slow)
        $newTerrainStability = $this->clamp($newTerrainStability, 0.0, 1.0);

        // 3. Biosphere vitality depends on stability and energy
        $newBiosphereVitality = $this->biosphereVitality
            + ($newLeyEnergy - 0.5) * 0.01 * $dt        // Energy above baseline helps
            + ($newTerrainStability - 0.5) * 0.01 * $dt  // Stability helps
            - $civilizationImpact * 0.01 * $dt;          // Civilization depletes
        $newBiosphereVitality = $this->clamp($newBiosphereVitality, 0.0, 1.0);

        // 4. Anomaly density â€” the TIPPING POINT mechanism
        // Anomalies grow when cosmic entropy is high AND terrain is unstable
        $anomalyGrowth = $cosmicState->entropy * (1.0 - $newTerrainStability) * 0.03 * $dt;
        $anomalyDecay = $newBiosphereVitality * 0.01 * $dt; // Healthy biosphere suppresses anomalies
        $newAnomalyDensity = $this->anomalyDensity + $anomalyGrowth - $anomalyDecay;

        // Tipping point: if anomaly density > 0.7, cascade effect
        if ($newAnomalyDensity > 0.7) {
            $newTerrainStability *= 0.95;  // Terrain degrades faster
            $newBiosphereVitality *= 0.90; // Biosphere takes a hit
            $newAnomalyDensity *= 0.6;     // Release (similar to fracture in cosmic layer)
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

    /**
     * How much the environment amplifies or dampens cosmic signals to civilization.
     * > 1.0 = amplifying, < 1.0 = dampening.
     */
    public function cosmicSignalMultiplier(): float
    {
        // Unstable terrain + high anomaly density amplifies cosmic effects
        $instability = 1.0 - $this->terrainStability;
        return 1.0 + ($instability * 0.3) + ($this->anomalyDensity * 0.5);
    }

    public function getHabitability(): float
    {
        return max(0.0, min(1.0, 
            ($this->terrainStability * 0.4) + 
            ($this->biosphereVitality * 0.6) - 
            ($this->anomalyDensity * 0.2)
        ));
    }

    /**
     * Environmental pressure that civilization experiences.
     * Higher = more hostile environment.
     */
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


