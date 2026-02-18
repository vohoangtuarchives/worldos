<?php

declare(strict_types=1);

namespace App\Domains\Saga\Services;

/**
 * Phase 4.2: Injects external shock into evolution (Saga mode) to measure resilience.
 */
final class ShockInjector
{
    private const SHOCK_TYPES = ['military', 'resource', 'ideology', 'tech'];

    public function __construct(
        private readonly bool $enabled = true,
        private readonly int $intervalYears = 75,
        private readonly float $magnitudeMin = 0.1,
        private readonly float $magnitudeMax = 0.3,
    ) {
    }

    public static function fromConfig(): self
    {
        $config = config('saga', []);
        return new self(
            (bool) ($config['shock_enabled'] ?? true),
            (int) ($config['shock_interval_years'] ?? 75),
            (float) ($config['shock_magnitude_min'] ?? 0.1),
            (float) ($config['shock_magnitude_max'] ?? 0.3),
        );
    }

    /**
     * Whether to inject shock in this tick (Saga mode).
     */
    public function shouldInject(int $sagaId, int $currentYear): bool
    {
        if (!$this->enabled || $currentYear <= 0) {
            return false;
        }
        return $currentYear % $this->intervalYears === 0;
    }

    /**
     * Magnitude of shock [0, 1].
     */
    public function magnitude(int $sagaId, int $currentYear): float
    {
        $min = $this->magnitudeMin;
        $max = $this->magnitudeMax;
        return $min + (mt_rand(0, 10000) / 10000.0) * ($max - $min);
    }

    /**
     * Type of shock: military, resource, ideology, tech.
     */
    public function shockType(int $sagaId, int $currentYear): string
    {
        $idx = $currentYear % count(self::SHOCK_TYPES);
        return self::SHOCK_TYPES[$idx];
    }
}
