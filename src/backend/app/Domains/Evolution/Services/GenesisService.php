<?php

namespace App\Domains\Evolution\Services;

use App\Domains\Evolution\Models\EvolutionProfile;
use App\Domains\Evolution\ValueObjects\StateVector;
use App\Domains\Narrative\Models\StoryPremise;
use App\Domains\Narrative\Models\MaterialSeed;
use App\Models\World;
use Illuminate\Support\Str;

class GenesisService
{
    /**
     * Incarnate a StoryPremise into a living World.
     *
     * @param StoryPremise $premise
     * @param string $initialPhase
     * @return World
     */
    public function incarnate(StoryPremise $premise, string $initialPhase = 'stable'): World
    {
        // 1. Resolve Components
        $components = $this->resolveComponents($premise);

        // 2. Generate Initial State Vector based on components
        $initialState = $this->calculateInitialState($components);

        // 3. Generate or Select Evolution Profile
        $profile = $this->generateProfile($components);

        // 4. Create the World
        // Note: We need to ensure the World model wraps the new state_vector column
        // For now, we assume World model casts state_vector to array or object
        
        $world = World::create([
            'id' => Str::uuid(),
            'name' => $premise->title,
            'slug' => Str::slug($premise->title . '-' . Str::random(4)),
            'description' => $premise->summary,
            'is_active' => true,
            'preset' => 'narrative_generated',
            'gene_vector' => $initialState->toArray(), // Use state vector as initial genome? Or separate? For now, sync them.
            'current_tick' => 0,
            'autonomous' => false,
            'entropy' => $initialState->entropy,
            'lifecycle_phase' => $initialPhase,
        ]);

        // 5. Create the Initial World State
        // We use the DB facade or relationship if defined.
        // Assuming World hasOne WorldState.
        // Wait, the migration added state_vector to world_states table.
        // We need to create a record in world_states linked to this world.
        
        $world->state()->create([
            'state_vector' => $initialState->toArray(),
            'evolution_profile_id' => $profile->id,
            'current_phase' => $initialPhase,
            // Legacy/Required fields from previous migrations
            'power_axis' => [], 
            'resource_axis' => [],
            'perception_axis' => [],
            'author_intent' => [],
            'structural_anchor' => 'Genesis',
        ]);

        return $world;
    }

    protected function resolveComponents(StoryPremise $premise): array
    {
        // Fetch the actual MaterialSeed objects
        $ids = array_values($premise->components);
        return MaterialSeed::whereIn('id', $ids)->get()->keyBy('type')->all();
    }

    protected function calculateInitialState(array $components): StateVector
    {
        // Default baseline
        $vector = [
            'coherence' => 0.5,
            'entropy' => 0.1,
            'belief_mass' => 0.1,
            'resource_flow' => 0.5,
            'stability' => 0.8,
            'innovation_rate' => 0.2,
            'contradiction_index' => 0.0,
        ];

        // Apply modifiers based on component types
        if (isset($components['power_system'])) {
            $name = strtolower($components['power_system']->name);
            if (str_contains($name, 'cultivation')) {
                $vector['belief_mass'] += 0.4; // High belief requirement
                $vector['innovation_rate'] = 0.1; // Traditional/Slow innovation
            } elseif (str_contains($name, 'system')) {
                $vector['coherence'] += 0.3; // Rigid rules
                $vector['resource_flow'] += 0.2; // Gamified rewards
            }
        }

        if (isset($components['social_structure'])) {
            $name = strtolower($components['social_structure']->name);
            if (str_contains($name, 'dystopia') || str_contains($name, 'corp')) {
                $vector['entropy'] += 0.3; // High chaos/inequality
                $vector['stability'] -= 0.2;
            } elseif (str_contains($name, 'sect')) {
                 $vector['coherence'] += 0.2; // Strict hierarchy
            }
        }

        if (isset($components['twist'])) {
            // Twists usually introduce instability or contradiction
            $vector['contradiction_index'] += 0.2;
            $vector['stability'] -= 0.1;
        }

        return StateVector::fromArray($vector);
    }

    protected function generateProfile(array $components): EvolutionProfile
    {
        // For now, return default, but ideally this matches the premise genre
        // e.g. "Cultivation" profile has high belief_growth coeff.
        
        // TODO: Create specific profiles based on seeds
        return EvolutionProfile::default();
    }
}
