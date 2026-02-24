<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Cosmology\ValueObjects\CosmicState;
use WorldOS\Legacy\Domain\Cosmology\ValueObjects\Attractor;

/**
 * WaveInterferenceEngine
 *
 * Core deterministic engine for cosmic-level energy oscillation.
 * Uses interference of 3 non-commensurate sine waves to produce
 * a quasi-periodic pattern that never repeats exactly.
 *
 * No randomness. Pure function. S(t) = F(t).
 */
class WaveInterferenceEngine
{
    // Wave periods (years) - chosen as co-prime multiples to avoid exact repetition
    private const WAVE_PERIODS = [
        5000,   // Primary harmonic
        11000,  // Secondary harmonic
        23000,  // Tertiary harmonic (grand cycle)
    ];

    // Wave amplitudes (relative strength of each harmonic)
    private const WAVE_AMPLITUDES = [
        0.20,   // Primary dominance
        0.12,   // Secondary modulation
        0.08,   // Tertiary envelope
    ];

    // Base energy level (equilibrium center)
    private const BASE_ENERGY = 0.60;

    /**
     * Calculate energy density at a given year.
     * Purely deterministic: same year always returns same value.
     *
     * @param int $year The cosmic year (can be negative for "past")
     * @return float Energy density value (bounded ~0.2 to ~1.0)
     */
    public function energyAt(int $year): float
    {
        $result = self::BASE_ENERGY;

        foreach (self::WAVE_PERIODS as $i => $period) {
            $amplitude = self::WAVE_AMPLITUDES[$i];
            $result += $amplitude * sin(2.0 * M_PI * $year / $period);
        }

        return max(0.0, min(1.0, $result));
    }

    /**
     * Calculate the rate of change of energy at a given year.
     * Used to determine cosmic "momentum" (is energy rising or falling?).
     *
     * @param int $year The cosmic year
     * @return float Rate of change (positive = rising, negative = falling)
     */
    public function energyDerivativeAt(int $year): float
    {
        $result = 0.0;

        foreach (self::WAVE_PERIODS as $i => $period) {
            $amplitude = self::WAVE_AMPLITUDES[$i];
            $omega = 2.0 * M_PI / $period;
            $result += $amplitude * $omega * cos($omega * $year);
        }

        return $result;
    }

    /**
     * Calculate the current dominant phase.
     * Returns a string describing which harmonic regime is dominant.
     *
     * @param int $year The cosmic year
     * @return string Phase description
     */
    public function dominantPhase(int $year): string
    {
        $contributions = [];

        foreach (self::WAVE_PERIODS as $i => $period) {
            $amplitude = self::WAVE_AMPLITUDES[$i];
            $contributions[$i] = abs($amplitude * sin(2.0 * M_PI * $year / $period));
        }

        $dominant = array_keys($contributions, max($contributions))[0];

        return match ($dominant) {
            0 => 'PRIMARY_HARMONIC',   // Fast oscillation
            1 => 'SECONDARY_HARMONIC', // Medium oscillation
            2 => 'TERTIARY_HARMONIC',  // Grand cycle
            default => 'UNKNOWN',
        };
    }

    /**
     * Get the wave constants for external inspection/debugging.
     */
    public function getConstants(): array
    {
        return [
            'periods' => self::WAVE_PERIODS,
            'amplitudes' => self::WAVE_AMPLITUDES,
            'base_energy' => self::BASE_ENERGY,
            'lcm_period' => $this->calculateLCM(),
        ];
    }

    /**
     * Calculate the LCM of all wave periods.
     * This is the theoretical "full repeat cycle" of the interference pattern.
     * For (5000, 11000, 23000), this is astronomically large.
     */
    private function calculateLCM(): int
    {
        $periods = self::WAVE_PERIODS;
        $lcm = $periods[0];

        for ($i = 1; $i < count($periods); $i++) {
            $lcm = intdiv($lcm * $periods[$i], $this->gcd($lcm, $periods[$i]));
        }

        return $lcm;
    }

    private function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }
        return $a;
    }
}
