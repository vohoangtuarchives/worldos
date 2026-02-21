<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

/**
 * AttractorFieldModifier
 *
 * Translates cosmic attractor state (DARK_AGE, EQUILIBRIUM, RENAISSANCE, CHAOS)
 * into concrete force modifications injected into the DynamicalKernel.
 *
 * Principle: attractor is NOT a label — it actively bends the force field.
 *   - DARK_AGE:    entropy amplification, cohesion suppression
 *   - CHAOS:       shock multiplier, legitimacy decay
 *   - RENAISSANCE: dissipation boost, knowledge/legitimacy growth
 *   - EQUILIBRIUM: gentle recovery, neutral forces
 */
final class AttractorFieldModifier
{
    /**
     * Accumulate attractor-driven force modifiers onto the existing ext forces.
     *
     * @param string  $attractorCode   Current cosmic attractor name (e.g. 'DARK_AGE')
     * @param array   $ext             Existing external forces keyed by StateVector::KEYS names
     * @param float   $cosmicEntropy   Cosmic entropy level [0–1] for intensity scaling
     * @return array Updated $ext forces
     */
    public function apply(string $attractorCode, array $ext, float $cosmicEntropy, float $ieNow = 0.0): array
    {
        $intensity = max(0.0, min(1.0, $cosmicEntropy));

        $code = strtoupper($attractorCode);

        return match (true) {
            $code === 'DARK_AGE'    => $this->applyDarkAge($ext, $intensity),
            $code === 'CHAOS'       => $this->applyChaos($ext, $intensity),
            $code === 'RENAISSANCE' => $this->applyRenaissance($ext, $intensity),
            default                 => $this->applyEquilibrium($ext, $intensity, $ieNow),
        };
    }

    // ───────────────────────────────────────────────────────────────
    // Attractor profiles
    // ───────────────────────────────────────────────────────────────

    private function applyDarkAge(array $ext, float $i): array
    {
        // Entropy amplification (+50%), legitimacy suppression, stability drain
        $ext['ie']        = ($ext['ie'] ?? 0.0) + 0.35 * $i;    // entropy push
        $ext['legitimacy'] = ($ext['legitimacy'] ?? 0.0) - 0.20 * $i;
        $ext['stability']  = ($ext['stability'] ?? 0.0) - 0.15 * $i;
        $ext['sc']         = ($ext['sc'] ?? 0.0) - 0.10 * $i;   // spiritual cohesion drops

        return $ext;
    }

    private function applyChaos(array $ext, float $i): array
    {
        // Shock multiplier — all dimensions noisy, legitimacy collapses fast
        $ext['ie']        = ($ext['ie'] ?? 0.0) + 0.50 * $i;
        $ext['legitimacy'] = ($ext['legitimacy'] ?? 0.0) - 0.30 * $i;
        $ext['mp']         = ($ext['mp'] ?? 0.0) + 0.20 * $i;   // military pressure rises
        $ext['inequality'] = ($ext['inequality'] ?? 0.0) + 0.15 * $i;

        return $ext;
    }

    private function applyRenaissance(array $ext, float $i): array
    {
        // RENAISSANCE is inverse: entropy DECREASES, culture grows
        // Note: ie force here is negative = dissipation
        $dissipation = 0.45 * (1.0 - $i * 0.5); // stronger when entropy is lower
        $ext['ie']         = ($ext['ie'] ?? 0.0) - $dissipation;
        $ext['legitimacy'] = ($ext['legitimacy'] ?? 0.0) + 0.20 * (1.0 - $i);
        $ext['ce']         = ($ext['ce'] ?? 0.0) + 0.15 * (1.0 - $i);  // cultural energy
        $ext['sc']         = ($ext['sc'] ?? 0.0) + 0.10 * (1.0 - $i);
        $ext['stability']  = ($ext['stability'] ?? 0.0) + 0.10 * (1.0 - $i);

        return $ext;
    }

    private function applyEquilibrium(array $ext, float $i, float $ieNow = 0.0): array
    {
        // Gentle homeostasis — push towards the middle [0.25, 0.40] band
        // When ie is high, it pulls down. When ie is too low, it pushes up.

        if ($ieNow > 0.4) {
            $ext['ie'] = ($ext['ie'] ?? 0.0) - 0.30 * ($ieNow - 0.4); // Pull down
        } elseif ($ieNow < 0.25) {
            $ext['ie'] = ($ext['ie'] ?? 0.0) + 0.15 * (0.25 - $ieNow); // Push up
        }
        
        $ext['stability']  = ($ext['stability'] ?? 0.0) + 0.05 * (1.0 - $i);
        $ext['legitimacy'] = ($ext['legitimacy'] ?? 0.0) + 0.03 * (1.0 - $i);

        return $ext;
    }
}
