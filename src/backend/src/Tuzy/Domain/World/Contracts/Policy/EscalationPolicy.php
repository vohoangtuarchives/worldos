<?php

namespace Tuzy\Domain\World\Contracts\Policy;

interface EscalationPolicy
{
    /**
     * Determine if the world tension should escalate based on current state.
     */
    public function evaluateEscalation(array $snapshot): float;
}
