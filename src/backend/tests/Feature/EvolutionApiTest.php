<?php

namespace Tests\Feature;

use App\Models\Evolution\Experiment;
use App\Models\Evolution\Generation;
use App\Models\UniverseModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EvolutionApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_experiment()
    {
        $response = $this->postJson('/api/v6/evolution/experiment', [
            'name' => 'Darwin Seed 001',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Darwin Seed 001')
            ->assertJsonPath('status', 'PENDING');

        $this->assertDatabaseHas('evolution_experiments', ['name' => 'Darwin Seed 001']);
    }

    public function test_can_list_experiments()
    {
        Experiment::create(['name' => 'Batch 1', 'status' => 'PENDING']);
        Experiment::create(['name' => 'Batch 2', 'status' => 'PENDING']);

        $response = $this->getJson('/api/v6/evolution/experiments');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_can_list_generations_for_experiment()
    {
        $experiment = Experiment::create(['name' => 'Multi-Gen test', 'status' => 'RUNNING']);
        
        Generation::create([
            'experiment_id' => $experiment->id,
            'generation_index' => 1,
            'population_size' => 10,
            'status' => 'COMPLETED'
        ]);

        Generation::create([
            'experiment_id' => $experiment->id,
            'generation_index' => 2,
            'population_size' => 15,
            'status' => 'PENDING'
        ]);

        $response = $this->getJson("/api/v6/evolution/experiments/{$experiment->id}/generations");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.generation_index', 1)
            ->assertJsonPath('1.generation_index', 2);
    }

    public function test_can_list_universes_for_generation()
    {
        $gen = Generation::create([
            'experiment_id' => '00000000-0000-0000-0000-000000000000', // Mock
            'generation_index' => 1,
            'population_size' => 10,
            'status' => 'COMPLETED'
        ]);

        UniverseModel::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Universe 1',
            'status' => 'COMPLETED',
            'world_blueprint_id' => '00000000-0000-0000-0000-000000000000',
            'generation_id' => $gen->id,
        ]);

        $response = $this->getJson("/api/v6/evolution/generations/{$gen->id}/universes");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Universe 1');
    }

    public function test_can_fetch_lineage_tree()
    {
        $gen = Generation::create([
            'experiment_id' => '00000000-0000-0000-0000-000000000000',
            'generation_index' => 1,
            'population_size' => 10,
            'status' => 'COMPLETED'
        ]);

        $adam = UniverseModel::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Adam Universe',
            'status' => 'COMPLETED',
            'world_blueprint_id' => '00000000-0000-0000-0000-000000000000',
            'generation_id' => $gen->id,
        ]);

        $child1 = UniverseModel::create([
            'id' => '22222222-2222-2222-2222-222222222222',
            'name' => 'Cain Universe',
            'status' => 'COMPLETED',
            'world_blueprint_id' => '00000000-0000-0000-0000-000000000000',
            'generation_id' => $gen->id,
            'parent_universe_id' => $adam->id,
        ]);

        $child2 = UniverseModel::create([
            'id' => '33333333-3333-3333-3333-333333333333',
            'name' => 'Abel Universe',
            'status' => 'COMPLETED',
            'world_blueprint_id' => '00000000-0000-0000-0000-000000000000',
            'generation_id' => $gen->id,
            'parent_universe_id' => $adam->id,
        ]);

        $grandchild = UniverseModel::create([
            'id' => '44444444-4444-4444-4444-444444444444',
            'name' => 'Enoch Universe',
            'status' => 'PENDING',
            'world_blueprint_id' => '00000000-0000-0000-0000-000000000000',
            'generation_id' => $gen->id,
            'parent_universe_id' => $child1->id,
        ]);

        $response = $this->getJson("/api/v6/evolution/universes/{$adam->id}/lineage");

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Adam Universe')
            ->assertJsonPath('children.0.name', 'Cain Universe')
            ->assertJsonPath('children.1.name', 'Abel Universe')
            ->assertJsonPath('children.0.children.0.name', 'Enoch Universe')
            ->assertJsonCount(2, 'children');
    }
}
