<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Theme;
use App\Models\ConflictPattern;
use App\Models\PowerSystem;
use App\Models\CharacterArchetype;
use Tuzy\Application\Genesis\Services\WorldSeedGenerator;
use Tuzy\Application\Genesis\Services\StoryGenerationService;
use Tuzy\Application\Genesis\Services\NoveltyScorer;
use Tuzy\Application\Genesis\Services\MotifUsageTracker;

class SimulateGenesisTest extends Command
{
    protected $signature = 'simulate:genesis-test';
    protected $description = 'Verify Phase 32 Narrative Genesis Engine';

    public function handle(
        WorldSeedGenerator $seedGenerator,
        StoryGenerationService $storyService,
        NoveltyScorer $noveltyScorer,
        MotifUsageTracker $usageTracker
    )
    {
        $this->info('Starting Genesis Engine Simulation...');

        // 1. Seed Registry Data (if empty)
        $this->seedRegistry();

        // 2. Generate World Seed
        $seedString = 'ProjectAntigravity';
        $seed = $seedGenerator->generate($seedString);
        $this->info("World Seed Generated: {$seed->id}");
        $this->info("Metaphysics: " . json_encode($seed->metaphysics_vector));
        $this->info("Instability: {$seed->instability_index}");

        // 3. Generate Story Blueprint
        $blueprint = $storyService->generateFromSeed($seed);
        $this->info("Story Blueprint Generated: {$blueprint->id}");
        $this->info("Theme: " . $blueprint->theme->name);
        $this->info("Conflict: " . $blueprint->conflict->name);
        $this->info("Power: " . $blueprint->powerSystem->name);

        // 4. Check Novelty
        $novelty = $noveltyScorer->calculateGlobalNovelty($blueprint);
        $this->info("Global Novelty Score: {$novelty}");

        // 5. Track Usage & Check Saturation
        $usageTracker->recordUsage($blueprint);
        $penalty = $usageTracker->getSaturationPenalty('theme', $blueprint->theme->id);
        $this->info("Saturation Penalty for Theme '{$blueprint->theme->name}': {$penalty}");

        if ($novelty >= 0.0 && $blueprint->id) {
            $this->info("SUCCESS: Genesis Engine operational.");
        } else {
            $this->error("FAILURE: Generation failed.");
        }
    }

    private function seedRegistry()
    {
        if (Theme::count() === 0) {
            Theme::create([
                'id' => Str::uuid(),
                'name' => 'Freedom vs Control',
                'philosophical_vector' => ['freedom' => 1.0],
                'moral_axis' => ['chaos' => 0.5],
                'emotional_axis' => ['rebellion' => 0.8]
            ]);
            Theme::create([
                'id' => Str::uuid(),
                'name' => 'Man vs Nature',
                'philosophical_vector' => ['survival' => 1.0],
                'moral_axis' => ['neutrality' => 1.0],
                'emotional_axis' => ['fear' => 0.6]
            ]);
        }

        if (ConflictPattern::count() === 0) {
            ConflictPattern::create([
                'id' => Str::uuid(),
                'type' => 'ideological',
                'name' => 'Revolution',
                'escalation_curve' => [],
                'resolution_modes' => []
            ]);
        }

        if (PowerSystem::count() === 0) {
            PowerSystem::create([
                'id' => Str::uuid(),
                'name' => 'Alchemy',
                'source_type' => 'scientific',
                'cost_model' => ['gold' => 10],
            ]);
        }

        if (CharacterArchetype::count() === 0) {
            CharacterArchetype::create([
                'id' => Str::uuid(),
                'name' => 'The Rebel',
                'desire_vector' => ['freedom' => 1.0],
                'fear_vector' => ['containment' => 1.0]
            ]);
            CharacterArchetype::create([
                'id' => Str::uuid(),
                'name' => 'The Tyrant',
                'desire_vector' => ['control' => 1.0],
                'fear_vector' => ['chaos' => 1.0]
            ]);
        }
    }
}
