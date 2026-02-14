<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\ValueObjects;

/**
 * Attractor - Represents a cosmic regime (a stable pattern the universe oscillates around).
 *
 * Each attractor has:
 * - An identity (code)
 * - A set of equilibrium parameters (the "center" of the basin)
 * - A threshold defining how much strain is needed to leave this attractor
 * - Rules for which attractors it can transition to
 */
final class Attractor
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly float $equilibriumEntropy,
        public readonly float $equilibriumEnergy,
        public readonly float $bifurcationThreshold, // Strain needed to leave
        public readonly array $transitionsTo,         // Codes of reachable attractors
    ) {}

    /**
     * Pre-defined cosmic regimes.
     * These are the "default" attractors. Bifurcation can create new ones.
     */
    public static function catalog(): array
    {
        return [
            'EQUILIBRIUM' => new self(
                code: 'EQUILIBRIUM',
                name: 'Trạng thái Cân bằng (Thiên Hòa)',
                equilibriumEntropy: 0.20,
                equilibriumEnergy: 0.60,
                bifurcationThreshold: 0.90,
                transitionsTo: ['HIGH_CHAOS', 'RESONANCE_DOMINANT'],
            ),
            'HIGH_CHAOS' => new self(
                code: 'HIGH_CHAOS',
                name: 'Hỗn Mang Dao Động (Thiên Loạn)',
                equilibriumEntropy: 0.65,
                equilibriumEnergy: 0.45,
                bifurcationThreshold: 1.10,
                transitionsTo: ['EQUILIBRIUM', 'VOID_COLLAPSE'],
            ),
            'RESONANCE_DOMINANT' => new self(
                code: 'RESONANCE_DOMINANT',
                name: 'Cộng Hưởng Thống Trị (Thiên Minh)',
                equilibriumEntropy: 0.35,
                equilibriumEnergy: 0.80,
                bifurcationThreshold: 0.85,
                transitionsTo: ['EQUILIBRIUM', 'HIGH_CHAOS'],
            ),
            'VOID_COLLAPSE' => new self(
                code: 'VOID_COLLAPSE',
                name: 'Hư Không Suy Kiệt (Thiên Diệt)',
                equilibriumEntropy: 0.85,
                equilibriumEnergy: 0.20,
                bifurcationThreshold: 1.30,
                transitionsTo: ['EQUILIBRIUM'],
            ),
        ];
    }

    /**
     * Get an attractor by code.
     */
    public static function find(string $code): ?self
    {
        return self::catalog()[$code] ?? null;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'equilibrium_entropy' => $this->equilibriumEntropy,
            'equilibrium_energy' => $this->equilibriumEnergy,
            'bifurcation_threshold' => $this->bifurcationThreshold,
            'transitions_to' => $this->transitionsTo,
        ];
    }
}
