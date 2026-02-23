<?php

declare(strict_types=1);

namespace Tests\Feature\Evolution\Database;

use App\Models\Evolution\CosmologicalFieldModel;
use App\Models\Evolution\Experiment;
use App\Models\Evolution\Generation;
use App\Models\Universe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EcosystemSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_build_lineage_tree_with_generations(): void
    {
        // 1. Create an Experiment
        $experiment = Experiment::create([
            'name' => 'Alpha Run',
            'status' => 'running',
        ]);

        // 2. Create Generation 1
        $gen1 = Generation::create([
            'experiment_id' => $experiment->id,
            'generation_index' => 1,
            'population_size' => 1,
            'status' => 'completed',
        ]);

        CosmologicalFieldModel::create([
            'generation_id' => $gen1->id,
            'entropy_background' => 0.5,
            'turbulence_pressure' => 0.2,
            'mythic_resonance_json' => ['ascension' => 0.5],
            'spectral_drift' => 0.05,
        ]);

        // 3. Create Root Universe (Gen 1)
        $rootUniverse = Universe::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Root G1',
            'world_blueprint_id' => 'bp_123',
            'multiverse_id' => 'mv_123',
            'generation_id' => $gen1->id,
            'parent_universe_id' => null,
            'state_vector' => [],
            'seed_dna' => ['myth' => 'genesis'],
        ]);

        // 4. Create Generation 2
        $gen2 = Generation::create([
            'experiment_id' => $experiment->id,
            'generation_index' => 2,
            'population_size' => 2,
            'status' => 'evaluating',
        ]);

        // 5. Create Child Universes (Gen 2) inheriting from Root
        $child1 = Universe::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Child A G2',
            'world_blueprint_id' => 'bp_123',
            'multiverse_id' => 'mv_123',
            'generation_id' => $gen2->id,
            'parent_universe_id' => $rootUniverse->id,
            'state_vector' => [],
            'seed_dna' => ['myth' => 'mutated_a'],
        ]);

        $child2 = Universe::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Child B G2',
            'world_blueprint_id' => 'bp_123',
            'multiverse_id' => 'mv_123',
            'generation_id' => $gen2->id,
            'parent_universe_id' => $rootUniverse->id,
            'state_vector' => [],
            'seed_dna' => ['myth' => 'mutated_b'],
        ]);

        // VERIFICATIONS
        
        // Assert Experiment has Generations
        $this->assertCount(2, $experiment->generations);
        $this->assertEquals('Alpha Run', $experiment->name);

        // Assert Generation 1 has 1 Universe
        $this->assertCount(1, $gen1->universes);
        $this->assertNotNull($gen1->cosmologicalField);

        // Assert Root Universe has 2 Children
        $this->assertCount(2, $rootUniverse->children);
        $this->assertEquals('Child A G2', $rootUniverse->children[0]->name);
        $this->assertEquals('Child B G2', $rootUniverse->children[1]->name);

        // Assert Child Universe has 1 Parent (Root)
        $this->assertEquals($rootUniverse->id, $child1->parent->id);
        $this->assertEquals($gen2->id, $child1->generation->id);
    }
}
