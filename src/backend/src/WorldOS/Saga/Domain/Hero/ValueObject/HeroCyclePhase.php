<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

/**
 * HeroCyclePhase — Defines the core narrative loop of the Projected Hero.
 *
 * This enum maps directly to the systemic tension/stress curve.
 * It strictly avoids literary fluff, focusing only on mathematical states.
 */
enum HeroCyclePhase: string
{
    /** Tension is low; conviction naturally recovers, stress decays. */
    case ACCUMULATION = 'accumulation';

    /** Tension/entropy is rising; stress accumulates, conviction buffers it. */
    case STRAIN = 'strain';

    /** Stress exceeded limit or critical anomaly hit; conviction plummets, trauma gained. */
    case COLLAPSE = 'collapse';

    /** Hero's dominant dimension surged while in collapse; conviction spikes, stress shatters. */
    case BREAKTHROUGH = 'breakthrough';

    /** Post-breakthrough or post-collapse recovery period before accumulation resumes. */
    case RESTABILIZATION = 'restabilization';

    public function isVulnerable(): bool
    {
        return $this === self::STRAIN || $this === self::COLLAPSE;
    }

    public function isRecovering(): bool
    {
        return $this === self::ACCUMULATION || $this === self::RESTABILIZATION;
    }
}
