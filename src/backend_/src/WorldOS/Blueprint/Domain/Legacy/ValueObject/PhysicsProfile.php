<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\ValueObject;

use InvalidArgumentException;

/**
 * Physics parameters for world simulation.
 *
 * @param float $instability_rate How fast entropy converts to instability (0.0 - 10.0)
 * @param float $decay_rate Natural energy loss per tick (0.0 - 1.0)
 * @param float $mutation_chance Probability of random mutation per tick (0.0 - 1.0)
 * @param float $dimensional_permeability How easily the world accepts external entropy (0.0 - 10.0)
 * @param float $entropy_cap Maximum entropy density before phase transition (100.0 - 10000.0)
 * @param float $energy_conservation_factor Efficiency of energy conversion (0.0 = total loss, 1.0 = perfect)
 */
readonly class PhysicsProfile
{
    public function __construct(
        public float $instability_rate,
        public float $decay_rate,
        public float $mutation_chance,
        public float $dimensional_permeability,
        public float $entropy_cap,
        public float $energy_conservation_factor,
    ) {
        $this->validate();
    }

    public static function standard(): self
    {
        return new self(
            instability_rate: 1.0,
            decay_rate: 0.05,
            mutation_chance: 0.01,
            dimensional_permeability: 1.0,
            entropy_cap: 1000.0,
            energy_conservation_factor: 0.8
        );
    }

    public static function void(): self
    {
        return new self(
            instability_rate: 0.1,
            decay_rate: 0.01,
            mutation_chance: 0.5,
            dimensional_permeability: 10.0,
            entropy_cap: 10000.0,
            energy_conservation_factor: 0.95
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            instability_rate: (float) ($data['instability_rate'] ?? 1.0),
            decay_rate: (float) ($data['decay_rate'] ?? 0.05),
            mutation_chance: (float) ($data['mutation_chance'] ?? 0.01),
            dimensional_permeability: (float) ($data['dimensional_permeability'] ?? 1.0),
            entropy_cap: (float) ($data['entropy_cap'] ?? 1000.0),
            energy_conservation_factor: (float) ($data['energy_conservation_factor'] ?? 0.8)
        );
    }

    public function toArray(): array
    {
        return [
            'instability_rate' => $this->instability_rate,
            'decay_rate' => $this->decay_rate,
            'mutation_chance' => $this->mutation_chance,
            'dimensional_permeability' => $this->dimensional_permeability,
            'entropy_cap' => $this->entropy_cap,
            'energy_conservation_factor' => $this->energy_conservation_factor,
        ];
    }

    public function drift(self $target, float $amount): self
    {
        $amount = max(0.0, min(1.0, $amount));
        return new self(
            instability_rate: $this->lerp($this->instability_rate, $target->instability_rate, $amount),
            decay_rate: $this->lerp($this->decay_rate, $target->decay_rate, $amount),
            mutation_chance: $this->lerp($this->mutation_chance, $target->mutation_chance, $amount),
            dimensional_permeability: $this->lerp($this->dimensional_permeability, $target->dimensional_permeability, $amount),
            entropy_cap: $this->lerp($this->entropy_cap, $target->entropy_cap, $amount),
            energy_conservation_factor: $this->lerp($this->energy_conservation_factor, $target->energy_conservation_factor, $amount),
        );
    }

    public function calculateDrift(self $baseline): float
    {
        $vars = [
            'instability_rate',
            'decay_rate',
            'mutation_chance',
            'dimensional_permeability',
            'energy_conservation_factor',
        ];
        $totalDiff = 0.0;
        foreach ($vars as $var) {
            $diff = $this->{$var} - $baseline->{$var};
            $totalDiff += $diff * $diff;
        }
        return min(1.0, sqrt($totalDiff) / count($vars));
    }

    private function lerp(float $start, float $end, float $amount): float
    {
        return $start + ($end - $start) * $amount;
    }

    private function validate(): void
    {
        if ($this->instability_rate < 0) {
            throw new InvalidArgumentException('Instability rate cannot be negative');
        }
        if ($this->decay_rate < 0 || $this->decay_rate > 1) {
            throw new InvalidArgumentException('Decay rate must be between 0 and 1');
        }
        if ($this->mutation_chance < 0 || $this->mutation_chance > 1) {
            throw new InvalidArgumentException('Mutation chance must be between 0 and 1');
        }
        if ($this->energy_conservation_factor < 0 || $this->energy_conservation_factor > 1) {
            throw new InvalidArgumentException('Energy conservation factor must be between 0 and 1');
        }
    }
}
