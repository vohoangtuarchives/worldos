<?php

namespace Tuzy\Application\Narrative\Dialogue\Services;

use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Domain\Narrative\ValueObject\Intent;
use Tuzy\Application\Narrative\Scene\Scene;
use Illuminate\Support\Facades\Log;

class SceneUpdater
{
    public function apply(Scene $scene, Character $actor, Intent $intent): void
    {
        // 1. Log the action (MVP: just a log, Real: Event Sourcing)
        Log::info("Action: {$actor->getName()} [{$intent->type}]", ['payload' => $intent->payload]);

        // 2. State Mutation based on Intent
        // For MVP, we'll simulate state changes by logging "EFFECT APPLIED"
        switch ($intent->type) {
            case 'REVEAL':
                Log::info("EFFECT: Information spread to other agents.");
                break;

            case 'EMOTIONAL_PRESSURE':
                Log::info("EFFECT: Target felt pressure.");
                break;
                
            case 'PROBE':
                Log::info("EFFECT: Question asked.");
                break;
        }
    }
}
