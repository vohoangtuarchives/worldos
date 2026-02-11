<?php

namespace App\StoryEngine;

class WorldState
{
    public int $tierIndex = 0;
    
    public \App\Domains\World\ValueObjects\WorldLawProfile $lawProfile;

    public function __construct()
    {
        $this->lawProfile = \App\Domains\World\ValueObjects\WorldLawProfile::default();
    }

    public int $publicAwareness = 5;
    public int $powerCenters = 2;
    /** @var FactionState[] */
    public array $factions = [];
}
