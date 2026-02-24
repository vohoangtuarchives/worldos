<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

/**
 * CivilizationPhaseDetector
 *
 * Detects the macro-phase of the dynamical system based on key indicators
 * to determine which "basin of attraction" the system is currently falling into.
 *
 * It creates a pitchfork bifurcation where the system branches into 
 * Empire (Golden Age), Chaos (High Entropy), or remains in a Linear (Metastable) basin.
 */
class CivilizationPhaseDetector
{
    public const PHASE_EMPIRE = 'empire';
    public const PHASE_CHAOS  = 'chaos';
    public const PHASE_LINEAR = 'linear';

    /**
     * Determines which dynamical basin the system should enter.
     */
    public function detect(CivilizationSnapshot $civ): string
    {
        // 1. Empire Basin Critical Mass 
        // High central control + high cultural output + moderate entropy
        if ($civ->legitimacy > 0.4 && $civ->culturalEnergy > 0.4 && $civ->internalEntropy < 0.6) {
            return self::PHASE_EMPIRE;
        }

        // 2. Chaos Basin Critical Mass
        // Entropy rising + lost of central control + moderate remaining energy to sustain conflict
        if ($civ->internalEntropy > 0.35 && $civ->stability < 0.4) {
            return self::PHASE_CHAOS;
        }

        // 3. Metastable Linear Basin
        // System is "floating" without enough force to collapse or escalate
        return self::PHASE_LINEAR;
    }
}
