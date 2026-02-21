<?php

namespace Tuzy\Domain\World\Policy;

use Tuzy\Domain\World\Contracts\Policy\EscalationPolicy;

class PassiveEscalationPolicy implements EscalationPolicy
{
    public function evaluateEscalation(array $snapshot): float
    {
        return 0.0;
    }
}
