<?php

namespace App\Domains\Narrative\LLM\Services;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use Illuminate\Support\Facades\Log;

class FakeLLMService implements LLMProvider
{
    public function generate(string $systemPrompt, string $userPrompt): array
    {
        Log::info("FakeLLM Generating...", [
            'system' => substr($systemPrompt, 0, 100) . '...',
            'user' => $userPrompt
        ]);

        // Deterministic logic for testing based on input keywords
        if (str_contains($userPrompt, 'Who are you?') || str_contains($systemPrompt, 'Hide Identity')) {
            return [
                'type' => 'DEFLECT',
                'payload' => ['topic' => 'Weather'],
                'confidence' => 0.95
            ];
        }

        if (str_contains($userPrompt, 'Find Truth')) {
             return [
                'type' => 'PROBE',
                'payload' => ['question' => 'Where were you last night?'],
                'confidence' => 0.85
            ];
        }

        // Default response
        return [
            'type' => 'IDLE',
            'payload' => ['thought' => 'Nothing to say.'],
            'confidence' => 0.5
        ];
    }
}
