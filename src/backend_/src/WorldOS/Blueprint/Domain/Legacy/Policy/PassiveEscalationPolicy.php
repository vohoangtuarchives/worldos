<?php

namespace WorldOS\Blueprint\Domain\Legacy\Policy;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\EscalationPolicy;

class PassiveEscalationPolicy implements EscalationPolicy
{
    public function evaluateEscalation(array $snapshot): float
    {
        return 0.0;
    }
}
