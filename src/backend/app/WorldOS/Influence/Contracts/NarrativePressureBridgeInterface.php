<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Contracts;

use App\WorldOS\Influence\ValueObjects\PressureSignal;
use App\WorldOS\Influence\ValueObjects\VectorForce;

/**
 * Narrative Pressure Bridge Contract.
 *
 * From docs §16.5: NarrativePressureBridgeInterface::injectPressure(PressureSignal)
 *
 * Converts narrative pressure signals into VectorForce deltas
 * that feed into the InfluencePipeline.
 */
interface NarrativePressureBridgeInterface
{
    /**
     * Convert a narrative pressure signal into a VectorForce.
     */
    public function injectPressure(PressureSignal $signal): VectorForce;
}
