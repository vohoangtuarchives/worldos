<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\EscalationPolicy;

class PassiveEscalationPolicy implements EscalationPolicy
{
    public function evaluateEscalation(array $snapshot): float
    {
        return 0.0;
    }
}
