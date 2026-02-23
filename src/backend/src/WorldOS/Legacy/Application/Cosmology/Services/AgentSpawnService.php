<?php

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Agents\LegendaryAgent;
use WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero;
use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\CosmologyRepository;

class AgentSpawnService
{
    // Thresholds for spawning
    private const ENTROPY_THRESHOLD_FOR_SAVIOR = 0.7;
    private const ORDER_THRESHOLD_FOR_REBEL = 0.8;
    
    public function __construct(
        private CosmologyRepository $repository
    ) {}

    /**
     * Attempt to spawn an agent in the universe based on its state.
     * Returns the Agent if spawned, null otherwise.
     */
    public function attemptSpawn(Universe $universe): ?LegendaryAgent
    {
        // 1. Check if universe already has an active agent (Limit 1 for now)
        // In a real implementation, we'd check the parameters or a separate agents collection
        $params = $universe->getParameters();
        if (isset($params['active_agent'])) {
            return null; // Already has a hero
        }

        $state = $universe->getState();
        $hero = null;
        $archetype = LegendaryAgent::ARCHETYPE_PROPHET;

        // 2. Logic: Crisis -> Savior
        if ($state->getEntropy() > self::ENTROPY_THRESHOLD_FOR_SAVIOR) {
            // High Entropy: Need a Military or Governance hero to restore order
            $hero = VietnameseHero::strongIn('military', 0.8)
                ->inRandomOrder()
                ->first();
            $archetype = LegendaryAgent::ARCHETYPE_WARLORD;
        } 
        // 3. Logic: Stagnation -> Rebel
        elseif ($state->getOrder() > self::ORDER_THRESHOLD_FOR_REBEL) {
            // High Order: Need a Revolutionary or Reformer
            $hero = VietnameseHero::where(function($q) {
                    $q->where('rebellion', '>', 0.7)
                      ->orWhere('reform', '>', 0.7);
                })
                ->inRandomOrder()
                ->first();
            $archetype = LegendaryAgent::ARCHETYPE_REVOLUTIONARY;
        }
        // 4. Logic: Golden Age -> Philosopher/Tycoon
        elseif ($state->getInnovation() > 0.7) {
            $hero = VietnameseHero::strongIn('philosophy', 0.8)
                ->inRandomOrder()
                ->first();
            $archetype = LegendaryAgent::ARCHETYPE_PROPHET;
        }

        // Fallback random if no specific condition but we want to force spawn (e.g. manual summon)
        if (!$hero) {
            return null;
        }

        // 5. Create Agent
        $agent = new LegendaryAgent($hero->name, $archetype, $hero);

        // 6. Bind to Universe
        // We store the agent in the universe parameters for persistence
        $params['active_agent'] = [
            'id' => $agent->id,
            'name' => $agent->name,
            'archetype' => $agent->archetype,
            'original_hero_id' => $agent->originalHeroId,
            'biography' => $agent->biography,
            'quote' => $agent->quote,
            'stats' => $agent->stats,
            'willpower' => $agent->willpower,
            'spawned_at_age' => $universe->getAge()
        ];
        
        // Update Universe Params logic in Entity (needs a setter or reflection, but Entity is immutable-ish)
        // We will reconstruct the universe
        $newUniverse = new Universe(
            $state,
            $params, // Updated params
            $universe->getId(),
            $universe->getAge(),
            $universe->getCoords(),
            $universe->getCosmicFactionId()
        );

        $this->repository->save($newUniverse);

        return $agent;
    }

    /**
     * Manually summon a specific hero
     */
    public function summonHero(Universe $universe, string $heroId): LegendaryAgent
    {
        $hero = VietnameseHero::findOrFail($heroId);
        
        // Determine Archetype based on stats
        $archetype = LegendaryAgent::ARCHETYPE_PROPHET;
        if ($hero->military > 0.8) $archetype = LegendaryAgent::ARCHETYPE_WARLORD;
        elseif ($hero->rebellion > 0.8) $archetype = LegendaryAgent::ARCHETYPE_REVOLUTIONARY;
        elseif ($hero->economic > 0.8) $archetype = LegendaryAgent::ARCHETYPE_TYCOON;

        $agent = new LegendaryAgent($hero->name, $archetype, $hero);

        $params = $universe->getParameters();
        $params['active_agent'] = [
            'id' => $agent->id,
            'name' => $agent->name,
            'archetype' => $agent->archetype,
            'original_hero_id' => $agent->originalHeroId,
            'biography' => $agent->biography,
            'quote' => $agent->quote,
            'stats' => $agent->stats,
            'willpower' => $agent->willpower,
            'spawned_at_age' => $universe->getAge()
        ];

        $newUniverse = new Universe(
            $universe->getState(),
            $params,
            $universe->getId(),
            $universe->getAge(),
            $universe->getCoords(),
            $universe->getCosmicFactionId()
        );

        $this->repository->save($newUniverse);

        return $agent;
    }
}
