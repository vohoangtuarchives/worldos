<?php

namespace WorldOS\Legacy\Application\World\Preset;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ConflictPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\EscalationPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\PowerLawPolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ResourcePolicy;
use WorldOS\Blueprint\Domain\Legacy\Contracts\WorldPreset;

class DynamicWorldPreset implements WorldPreset
{
    public function __construct(
        private string $id,
        private string $code,
        private string $name,
        private PowerLawPolicy $powerPolicy,
        private ResourcePolicy $resourcePolicy,
        private ConflictPolicy $conflictPolicy,
        private EscalationPolicy $escalationPolicy,
        private ?object $mythPolicy = null,
        private ?object $scarPolicy = null,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function powerLaw(): PowerLawPolicy
    {
        return $this->powerPolicy;
    }

    public function resourceLaw(): ResourcePolicy
    {
        return $this->resourcePolicy;
    }

    public function conflictLaw(): ConflictPolicy
    {
        return $this->conflictPolicy;
    }

    public function escalationLaw(): EscalationPolicy
    {
        return $this->escalationPolicy;
    }

    public function mythLaw(): ?object
    {
        return $this->mythPolicy;
    }

    public function scarLaw(): ?object
    {
        return $this->scarPolicy;
    }
}
