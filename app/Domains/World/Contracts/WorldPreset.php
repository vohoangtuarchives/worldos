<?php

namespace App\Domains\World\Contracts;

use App\Domains\World\Contracts\Policy\ConflictPolicy;
use App\Domains\World\Contracts\Policy\EscalationPolicy;
use App\Domains\World\Contracts\Policy\PowerLawPolicy;
use App\Domains\World\Contracts\Policy\ResourcePolicy;

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
