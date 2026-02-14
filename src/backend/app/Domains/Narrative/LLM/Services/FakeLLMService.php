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

        if (str_contains($userPrompt, 'Aggressor World')) {
            return [
                'description' => 'Một vết nứt màu tím rực rỡ hiện ra trên bầu trời, mang theo hơi thở của một thực tại khác lạ đang xâm chiếm nơi đây.',
                'type' => 'REVEAL',
                'confidence' => 0.99
            ];
        }

        if (str_contains($userPrompt, 'Entropy')) {
            return [
                'description' => 'Không gian xung quanh trở nên mờ ảo và nóng nực, như thể những hạt hạ nguyên tử đang khiêu vũ trong vũ điệu của sự hủy diệt.',
                'type' => 'REVEAL',
                'confidence' => 0.99
            ];
        }

        // Default response
        return [
            'type' => 'IDLE',
            'description' => 'Một sự kiện chấn động đang diễn ra trong dòng chảy thực tại.',
            'payload' => ['thought' => 'Nothing to say.'],
            'confidence' => 0.5
        ];
    }
}
