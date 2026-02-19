<?php

namespace App\Domains\Narrative\Dialogue\Services;

use App\Domains\Narrative\Character\Character;
use App\Domains\Narrative\Scene\Scene;
use App\Domains\Narrative\Dialogue\ValueObjects\Intent;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\Domains\Narrative\LLM\Services\ContextBuilder;
use App\Domains\Narrative\LLM\Services\IntentParser;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\AI\AIAgentContext;

class DialogueEngine
{
    public function __construct(
        protected TurnScheduler $scheduler,
        protected ConsistencyGuard $guard,
        protected SceneUpdater $updater,
        // New Dependencies
        protected LLMProvider $llm,
        protected ContextBuilder $contextBuilder,
        protected IntentParser $intentParser
    ) {}

    public function runTurn(Scene $scene): void
    {
        if ($scene->isResolved()) {
            return;
        }

        // 1. Scheduler picks actor
        $actor = $this->scheduler->pick($scene);
        
        if (!$actor) {
            Log::info("Scheduler picked no actor.");
            return;
        }

        // 2. Build Context Prompt
        $systemPrompt = $this->contextBuilder->build($actor, $scene);
        
        // 3. Get LLM Output
        // For User Prompt, we might pass the last event or "It is your turn."
        // In full system, this comes from Scene History.
        $userPrompt = "It is your turn to act. Select intent.";

        try {
            $rawJson = app(AIAgentContext::class)->runWith('narrative.dialogue', fn () => $this->llm->generate($systemPrompt, $userPrompt));
            
            // 4. Parse Intent
            $intent = $this->intentParser->parse($rawJson);

            // 5. Guard Validation
            $this->guard->validate($actor, $intent, $scene);

            // 6. Update Scene
            $this->updater->apply($scene, $actor, $intent);

        } catch (Exception $e) {
            Log::warning("Guard/LLM blocked {$actor->getName()}: {$e->getMessage()}");
            // Retry logic would go here (re-prompt with error message)
        }
    }
}
