<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Application\Narrative\Character\MemoryCollection;
use Tuzy\Application\Narrative\Character\GoalStack;
use Tuzy\Application\Narrative\Character\Entities\Memory;
use Tuzy\Domain\Narrative\ValueObject\EmotionState;

class CharacterAggregateTest extends TestCase
{
    public function test_character_can_remember_facts()
    {
        // 1. Setup
        $memories = new MemoryCollection();
        $emotions = [];
        $goals = new GoalStack();
        
        $char = new Character('uuid-1', 'Test Agent', $memories, $emotions, $goals);

        // 2. Act
        $memory = new Memory('m1', 'semantic', 'Sky is blue', 'public', 1.0);
        $char->remember($memory);

        // 3. Assert
        $this->assertCount(1, $char->getMemories()->all());
        $this->assertEquals('Sky is blue', $char->getMemories()->all()[0]->content);
    }

    public function test_character_emotions_change_and_decay()
    {
        $char = new Character('uuid-1', 'Test Agent', new MemoryCollection(), [], new GoalStack());

        // 1. Trigger Fear
        $char->feel('fear', 0.5);
        $this->assertEquals(0.5, $char->getEmotions()->get('fear')->intensity);

        // 2. Amplify Fear
        $char->feel('fear', 0.2);
        $this->assertEquals(0.7, $char->getEmotions()->get('fear')->intensity);

        // 3. Decay Logic (decay rate is 0.1 by default in our implementation)
        $char->decayEmotions();
        // 0.7 * (1.0 - 0.1) = 0.63
        $this->assertEquals(0.63, $char->getEmotions()->get('fear')->intensity);
    }

    public function test_goal_stack_prioritization()
    {
        $stack = new GoalStack();
        $stack->add(['description' => 'Low Priority', 'priority' => 10, 'status' => 'active']);
        $stack->add(['description' => 'High Priority', 'priority' => 100, 'status' => 'active']);
        $stack->add(['description' => 'Completed High', 'priority' => 200, 'status' => 'completed']);

        $highest = $stack->getHighestPriority();

        $this->assertEquals('High Priority', $highest['description']);
    }
}
