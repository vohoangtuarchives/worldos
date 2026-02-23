<?php

namespace WorldOS\Legacy\Application\Cosmology\Agents;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Cosmology\Mathematics\Vector;
use Illuminate\Support\Str;

class LegendaryAgent
{
    public const ARCHETYPE_WARLORD = 'WARLORD';
    public const ARCHETYPE_PROPHET = 'PROPHET';
    public const ARCHETYPE_TYCOON = 'TYCOON';
    public const ARCHETYPE_REVOLUTIONARY = 'REVOLUTIONARY';

    public string $id;
    public string $name;
    public string $archetype;
    public float $willpower; // 0.0 to 1.0 (Resource to burn)
    public float $destinyDebt; // Accumulates with Defy Fate
    
    // Phase 32: Historical Context
    public ?string $originalHeroId = null;
    public ?string $biography = null;
    public ?string $quote = null;
    public array $stats = [];

    public function __construct(string $name, string $archetype, ?\WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero $hero = null)
    {
        $this->id = (string) Str::uuid();
        $this->name = $name;
        $this->archetype = $archetype;
        $this->willpower = 1.0;
        $this->destinyDebt = 0.0;
        
        if ($hero) {
            $this->originalHeroId = $hero->id;
            $this->biography = $hero->biography;
            $this->quote = $hero->quote;
            $this->stats = $hero->topDimensions;
        }
    }

    /**
     * The Act of Defying Fate.
     * Allows the agent to force a specific outcome against the flow of history.
     * 
     * Cost:
     * 1. Burns Willpower.
     * 2. Generates 'Cosmic Scar' (Trauma) in the world.
     * 3. Increases Destiny Debt (probability of personal tragedy).
     */
    public function defyFate(WorldStateVector $currentWorldState, string $intention): WorldStateVector
    {
        if ($this->willpower < 0.1) {
            // Not enough will to break reality
            return $currentWorldState;
        }

        $impact = $this->calculateImpactVector($intention);
        
        // The cost of the miracle
        $this->willpower -= 0.3;
        $this->destinyDebt += 0.2;

        // Apply impact
        $intermediate = $currentWorldState->add($impact);
        $nextComponents = $intermediate->getAll();

        // Cosmic Scar: The universe bleeds when you cut it
        // Trauma increases by 0.1
        $nextComponents[WorldStateVector::DIMENSION_TRAUMA] += 0.1;
        
        return new WorldStateVector($nextComponents);
    }

    protected function calculateImpactVector(string $intention): Vector
    {
        $d = [];
        // Default small impact
        $magnitude = 0.15; 

        switch ($intention) {
            case 'RESTORE_ORDER':
                // Warlord/Prophet imposes order
                $d[WorldStateVector::DIMENSION_ORDER] = $magnitude;
                $d[WorldStateVector::DIMENSION_ENTROPY] = -$magnitude;
                break;
            
            case 'INCITE_CHAOS':
                // Revolutionary burns it down
                $d[WorldStateVector::DIMENSION_ENTROPY] = $magnitude;
                $d[WorldStateVector::DIMENSION_ORDER] = -$magnitude;
                $d[WorldStateVector::DIMENSION_INEQUALITY] = -$magnitude * 0.5; // Leveling
                break;

            case 'ACCUMULATE_POWER':
                // Tycoon/Warlord
                $d[WorldStateVector::DIMENSION_ELITE_COHESION] = $magnitude;
                $d[WorldStateVector::DIMENSION_RESOURCE_STOCK] = $magnitude;
                $d[WorldStateVector::DIMENSION_INEQUALITY] = $magnitude * 0.5;
                break;

            case 'HEAL_WORLD':
                // Prophet
                $d[WorldStateVector::DIMENSION_TRAUMA] = -$magnitude;
                $d[WorldStateVector::DIMENSION_COHESION] = $magnitude;
                break;
        }

        return new Vector($d);
    }
}
