<?php

namespace WorldOS\Blueprint\Domain\Legacy\Contracts;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ConflictPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\EscalationPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\PowerLawPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ResourcePolicy;

interface WorldPreset
{
    public function id(): string;
    public function code(): string;
    public function name(): string;

    public function powerLaw(): PowerLawPolicy;
    public function resourceLaw(): ResourcePolicy;
    public function conflictLaw(): ConflictPolicy;
    public function escalationLaw(): EscalationPolicy;
    
    // Optional
    public function mythLaw(): ?object; // To be typed strictly later if needed
    public function scarLaw(): ?object;
}
