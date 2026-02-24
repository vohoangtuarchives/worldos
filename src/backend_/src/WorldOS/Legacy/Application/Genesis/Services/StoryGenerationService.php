<?php

namespace WorldOS\Legacy\Application\Genesis\Services;

use App\Models\GenesisSeed;
use App\Models\StoryBlueprint;
use App\Models\Theme;
use App\Models\ConflictPattern;
use App\Models\PowerSystem;
use App\Models\CharacterArchetype;
use Illuminate\Support\Str;

class StoryGenerationService
{
    public function __construct(
        private WorldSeedGenerator $seedGenerator
    ) {}

    /**
     * Generate a full Story Blueprint from a Seed.
     */
    public function generateFromSeed(GenesisSeed $seed): StoryBlueprint
    {
        // 1. Select Theme based on Seed Tags/Metaphysics
        // For now, random selection, eventually biased by vector similarity
        $theme = Theme::inRandomOrder()->first();
        
        // 2. Select Conflict Pattern
        // Filter by theme compatibility if available
        $conflict = ConflictPattern::inRandomOrder()->first();

        // 3. Select Power System
        // Biased by Metaphysics (Tinh -> Bio/Martial, Khi -> Magic, Than -> Eldritch)
        $powerSystem = PowerSystem::inRandomOrder()->first();

        // 4. Select Archetypes
        $protagonist = CharacterArchetype::inRandomOrder()->first();
        $antagonist = CharacterArchetype::inRandomOrder()->where('id', '!=', $protagonist->id ?? '')->first();

        // 5. Structure Vector (Generated)
        $structureVector = [
            'pacing' => $seed->instability_index > 0.6 ? 'fast' : 'slow',
            'tone' => in_array('grimdark', $seed->tags ?? []) ? 'dark' : 'heroic',
            'complexity' => mt_rand(1, 100) / 100
        ];

        // 6. Calculate Novelty (Placeholder)
        $noveltyScore = 0.8; // High novelty!

        return StoryBlueprint::create([
            'id' => Str::uuid(),
            'genesis_seed_id' => $seed->id,
            'theme_id' => $theme->id,
            'conflict_id' => $conflict->id,
            'power_system_id' => $powerSystem->id,
            'protagonist_archetype_id' => $protagonist->id,
            'antagonist_archetype_id' => $antagonist->id,
            'novelty_score' => $noveltyScore,
            'structure_vector' => $structureVector,
        ]);
    }
}
