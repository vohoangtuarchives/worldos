<?php

namespace App\StoryEngine\Simulation;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\Seed;

class SimulationContext
{
    public function __construct(
        public WorldState $world,
        public CharacterState $character,
        public array $seeds, // Active seeds
        public string $timelineId,
        public int $currentChapter,
        public ?string $worldId = null,
        public bool $safeMode = false
    ) {}

    /** @var Seed|null The seed picked for resolution in this tick */
    public ?Seed $activeSeed = null;
    
    /** @var array Metrics collected during this tick */
    public array $metrics = [];

    public function addSeed(Seed $seed): void
    {
        $this->seeds[] = $seed;
    }

    public function removeSeed(Seed $target): void
    {
        foreach ($this->seeds as $key => $s) {
            if ($s === $target) {
                unset($this->seeds[$key]);
                break;
            }
        }
        $this->seeds = array_values($this->seeds);
    }
}
