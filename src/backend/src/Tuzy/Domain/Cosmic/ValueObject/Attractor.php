<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmic\ValueObject;

/**
 * Cosmic regime (stable pattern the universe oscillates around).
 * identity (code), equilibrium params, bifurcation threshold, transitions.
 */
readonly class Attractor
{
    public function __construct(
        public string $code,
        public string $name,
        public float $equilibriumEntropy,
        public float $equilibriumEnergy,
        public float $bifurcationThreshold,
        /** @var list<string> */
        public array $transitionsTo,
    ) {
    }

    /** Pre-defined cosmic regimes. */
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
