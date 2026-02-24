<?php

namespace App\StoryEngine;

class MemoryRecorder
{
    /**
     * @param FactionState $faction
     * @param string $intentName The key of the action/intent (e.g., 'expand_territory', 'declare_war')
     * @param string $outcome success | failure | disaster
     */
    public static function recordOutcome(
        FactionState $faction,
        string $intentName,
        string $outcome
    ): void {
        $key = $intentName;

        if ($outcome === 'success') {
            $faction->memory->successCounter[$key] =
                ($faction->memory->successCounter[$key] ?? 0) + 1;
        } else {
            $faction->memory->failureCounter[$key] =
                ($faction->memory->failureCounter[$key] ?? 0) + 1;
        }

        if ($outcome === 'disaster') {
            $faction->memory->traumaTags[] = $key . '_disaster';
        }
    }
}
