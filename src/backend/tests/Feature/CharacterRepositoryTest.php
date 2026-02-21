<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\World;
use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Application\Narrative\Character\MemoryCollection;
use Tuzy\Application\Narrative\Character\GoalStack;
use Tuzy\Application\Narrative\Character\Entities\Memory;
use Tuzy\Application\Narrative\Character\Repositories\CharacterEloquentRepository;
use Illuminate\Support\Str;

class CharacterRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_persist_and_retrieve_character_aggregate()
    {
        // 1. Setup World
        $world = World::create(['name' => 'Test World']);

        // 2. Create Aggregate
        $charId = (string) Str::uuid();
        $memories = new MemoryCollection();
        $memories->add(new Memory((string) Str::uuid(), 'semantic', 'Fire burns', 'public', 1.0));
        
        $goals = new GoalStack();
        $goals->add(['description' => 'Survive', 'priority' => 100, 'status' => 'active']);

        $character = new Character(
            $charId,
            'Hero',
            $memories,
            [], // No emotions initially
            $goals
        );

        // 3. Save
        $repo = new CharacterEloquentRepository();
        $repo->save($character, $world->id);

        // 4. Assert DB
        $this->assertDatabaseHas('characters', ['id' => $charId, 'name' => 'Hero']);
        $this->assertDatabaseHas('character_memories', ['content' => 'Fire burns']);
        $this->assertDatabaseHas('character_goals', ['description' => 'Survive']);

        // 5. Retrieve & Verify
        $retrieved = $repo->findById($charId);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Hero', $retrieved->getName());
        $this->assertCount(1, $retrieved->getMemories()->all());
        $this->assertEquals('Fire burns', $retrieved->getMemories()->all()[0]->content);

        // 6. Modify & Resave
        $retrieved->feel('fear', 0.5);
        $repo->save($retrieved, $world->id);

        $this->assertDatabaseHas('character_emotions', ['character_id' => $charId, 'type' => 'fear', 'intensity' => 0.5]);
    }
}
