<?php

namespace Tuzy\Application\Cosmology\Agents;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Faction
{
    private string $id;
    private string $name;
    private float $radicalization = 0.0;
    private float $power = 0.0;
    
    /** @var Collection<string, TranscendentAgent> */
    private Collection $members;

    public function __construct(string $name, ?string $id = null)
    {
        $this->id = $id ?? (string) Str::uuid();
        $this->name = $name;
        $this->members = new Collection();
    }

    public function addMember(TranscendentAgent $agent): void
    {
        $this->members->put($agent->getId(), $agent);
        // Recalculate power based on members?
        $this->power += 0.1; // Simple increment for now
    }

    public function getRadicalization(): float
    {
        return $this->radicalization;
    }

    public function setRadicalization(float $value): void
    {
        $this->radicalization = max(0.0, min(1.0, $value));
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }
}
