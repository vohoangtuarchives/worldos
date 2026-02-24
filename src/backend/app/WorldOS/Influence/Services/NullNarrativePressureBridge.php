<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Services;

use App\WorldOS\Influence\Contracts\NarrativePressureBridgeInterface;
use App\WorldOS\Influence\ValueObjects\PressureSignal;
use App\WorldOS\Influence\ValueObjects\VectorForce;

/**
 * Null Narrative Pressure Bridge — no-op stub.
 *
 * From docs §16.5: stub NullNarrativePressureBridge.
 *
 * Used until the Narrative module is built. Returns zero force.
 */
final class NullNarrativePressureBridge implements NarrativePressureBridgeInterface
{
    public function injectPressure(PressureSignal $signal): VectorForce
    {
        return VectorForce::zero();
    }
}
