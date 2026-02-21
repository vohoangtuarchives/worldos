<?php

namespace Tuzy\Application\Narrative\LLM\Services;

use Tuzy\Application\Narrative\Character\Character;
use Tuzy\Application\Narrative\Scene\Scene;

class ContextBuilder
{
    /**
     * Build the System Prompt for the LLM.
     * Includes Character Profile, Memories, and Scene Context.
     */
    public function build(Character $actor, Scene $scene): string
    {
        $prompt = "You are a fictional character in a story. Do not break character. Do not act as an AI.\n\n";

        // 1. Identity
        $prompt .= "Your Name: {$actor->getName()}\n";
        // $prompt .= "Personality: " . json_encode($actor->getPersonality(), JSON_PRETTY_PRINT) . "\n\n";

        // 2. State
        $sentiment = $this->formatEmotions($actor);
        $prompt .= "Current Emotional State: {$sentiment}\n";

        // 3. Goals
        $goals = $this->formatGoals($actor);
        $prompt .= "Current Goals (Priority Order):\n{$goals}\n";

        // 4. Memory (Relevant Context)
        // In real app, we fetch relevant by vector search.
        // For MVP, we pass recent 5 memories.
        $memories = $this->formatMemories($actor);
        $prompt .= "Relevant Memories:\n{$memories}\n";

        // 5. Scene Context
        $prompt .= "Current Scene Goal: {$scene->goal}\n";
        $prompt .= "Other Agents: " . $scene->activeAgents->pluck('name')->implode(', ') . "\n\n";

        // 6. JSON Format Instruction
        $prompt .= "Output Format: Respond with a JSON object ONLY.\n";
        $prompt .= "Schema: { 'type': 'INTENT_TYPE', 'payload': { ... }, 'confidence': 0.0-1.0 }\n";
        $prompt .= "Allowed Types: PROBE, REVEAL, DEFLECT, EMOTIONAL_PRESSURE, IDLE.\n";

        return $prompt;
    }

    protected function formatEmotions(Character $actor): string
    {
        return $actor->getEmotions()->map(fn($e) => "{$e->type}: {$e->intensity}")->implode(', ');
    }

    protected function formatGoals(Character $actor): string
    {
        return collect($actor->getGoals()->all())
            ->where('status', 'active')
            ->sortByDesc('priority')
            ->map(fn($g) => "- {$g['description']} (P:{$g['priority']})")
            ->implode("\n");
    }

    protected function formatMemories(Character $actor): string
    {
        // Limit to 5 recent for MVP context window
        return collect($actor->getMemories()->all())
            ->take(-5)
            ->map(fn($m) => "- [{$m->type}] {$m->content} (Confidence: {$m->confidence})")
            ->implode("\n");
    }
}
