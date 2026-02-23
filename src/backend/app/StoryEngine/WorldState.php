<?php

namespace App\StoryEngine;

class WorldState
{
    public int $tierIndex = 0;
    
    public \WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile $lawProfile;

    public function __construct()
    {
        $this->lawProfile = \WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile::default();
    }

    public int $publicAwareness = 5;
    public int $powerCenters = 2;
    /** @var FactionState[] */
    public array $factions = [];
}
