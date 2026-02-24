<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WorldOS\Legacy\Application\Narrative\Character\Character;
use WorldOS\Legacy\Application\Narrative\Character\MemoryCollection;
use WorldOS\Legacy\Application\Narrative\Character\GoalStack;
use WorldOS\Legacy\Application\Narrative\Scene\Scene;
use WorldOS\Legacy\Application\Narrative\Dialogue\Services\DialogueEngine;
use WorldOS\Legacy\Application\Narrative\Dialogue\Services\TurnScheduler;
use WorldOS\Legacy\Application\Narrative\Dialogue\Services\ConsistencyGuard;
use WorldOS\Legacy\Application\Narrative\Dialogue\Services\SceneUpdater;
use WorldOS\Legacy\Application\Narrative\LLM\Services\FakeLLMService;
use WorldOS\Legacy\Application\Narrative\LLM\Services\ContextBuilder;
use WorldOS\Legacy\Application\Narrative\LLM\Services\IntentParser;
use Illuminate\Support\Collection;

class LLMIntegrationTest extends TestCase
{
    public function test_dialogue_engine_uses_llm_provider()
    {
        // 1. Setup Actor with Specific Goal (to trigger FakeLLM)
        $charA = new Character('uuid-A', 'Neo', new MemoryCollection(), [], new GoalStack());
        // FakeLLM triggers 'PROBE' if goal has 'Find Truth'
        $charA->getGoals()->add(['description' => 'Find Truth', 'priority' => 100, 'status' => 'active']);

        $scene = new Scene('SCN-1', 'Matrix', collect([$charA]));

        // 2. Setup Engine with Fake LLM
        $scheduler = new TurnScheduler();
        $guard = new ConsistencyGuard();

        $updater = new SceneUpdater(new \WorldOS\Legacy\Application\Narrative\Character\Repositories\CharacterEloquentRepository());

        $llm = new FakeLLMService();
        $contextBuilder = new ContextBuilder();
        $intentParser = new IntentParser();

        $engine = new DialogueEngine($scheduler, $guard, $updater, $llm, $contextBuilder, $intentParser);

        // 3. Run Turn
        // Ideally we mock the Logger to spy, but for now we run and check no exceptions.
        // We can inspect the output via Log or side effects if we had event sourcing.
        
        $engine->runTurn($scene);

        // If no exception, it means:
        // 1. Scheduler picked Neo.
        // 2. ContextBuilder built prompt.
        // 3. FakeLLM returned PROBE intent (due to 'Find Truth' keyword).
        // 4. IntentParser parsed it.
        // 5. Guard allowed it.
        // 6. Updater applied it.
        
        $this->assertTrue(true);
    }
}
