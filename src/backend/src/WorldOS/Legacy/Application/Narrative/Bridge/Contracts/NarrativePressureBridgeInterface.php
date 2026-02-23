<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Bridge\Contracts;

use WorldOS\Saga\Domain\Narrative\ValueObject\PressureSignal;

/**
 * WorldOS 2.0 Clean: Inject narrative (or other) pressure into runtime so PhaseEngine can
 * consider it for phase transition / collapse, without mutating Universe vector directly.
 */
interface NarrativePressureBridgeInterface
{
    public function injectPressure(PressureSignal $signal): void;
}
