<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge;

use App\Domains\Narrative\Bridge\Contracts\NarrativePressureBridgeInterface;
use App\Domains\Narrative\Bridge\DTO\PressureSignal;

/**
 * No-op implementation. When narrative_affects_via_pressure is implemented for real,
 * a bridge that writes to a pressure store or calls PhaseEngine.injectPressure() can be bound instead.
 */
final class NullNarrativePressureBridge implements NarrativePressureBridgeInterface
{
    public function injectPressure(PressureSignal $signal): void
    {
        // No-op.
    }
}
