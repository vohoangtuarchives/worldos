<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge\Contracts;

use App\Domains\Narrative\Bridge\DTO\PressureSignal;

/**
 * WorldOS 2.0 Clean: Inject narrative (or other) pressure into runtime so PhaseEngine can
 * consider it for phase transition / collapse, without mutating Universe vector directly.
 */
interface NarrativePressureBridgeInterface
{
    public function injectPressure(PressureSignal $signal): void;
}
