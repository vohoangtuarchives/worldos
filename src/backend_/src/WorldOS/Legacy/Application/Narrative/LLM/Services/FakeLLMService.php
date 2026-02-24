<?php

namespace WorldOS\Legacy\Application\Narrative\LLM\Services;

use WorldOS\Legacy\Application\Narrative\LLM\Contracts\LLMProvider;
use WorldOS\Legacy\Application\Narrative\LLM\Support\AIProviderRequestLogger;
use Illuminate\Support\Facades\Log;

class FakeLLMService implements LLMProvider
{
    public function __construct(
        protected ?AIProviderRequestLogger $requestLogger = null,
    ) {}

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $start = microtime(true);

        Log::info('FakeLLM Generating...', [
            'system' => substr($systemPrompt, 0, 100) . '...',
            'user' => $userPrompt,
        ]);

        // Deterministic logic for testing based on input keywords
        if (str_contains($userPrompt, 'Who are you?') || str_contains($systemPrompt, 'Hide Identity')) {
            return $this->withHistory(
                [
                    'type' => 'DEFLECT',
                    'payload' => ['topic' => 'Weather'],
                    'confidence' => 0.95,
                ],
                $systemPrompt,
                $userPrompt,
                $start,
            );
        }

        if (str_contains($userPrompt, 'Find Truth')) {
            return $this->withHistory(
                [
                    'type' => 'PROBE',
                    'payload' => ['question' => 'Where were you last night?'],
                    'confidence' => 0.85,
                ],
                $systemPrompt,
                $userPrompt,
                $start,
            );
        }

        if (str_contains($userPrompt, 'Aggressor World')) {
            return $this->withHistory(
                [
                    'description' => 'Một vết nứt màu tím rực rỡ hiện ra trên bầu trời, mang theo hơi thở của một thực tại khác lạ đang xâm chiếm nơi đây.',
                    'type' => 'REVEAL',
                    'confidence' => 0.99,
                ],
                $systemPrompt,
                $userPrompt,
                $start,
            );
        }

        if (str_contains($userPrompt, 'Entropy')) {
            return $this->withHistory(
                [
                    'description' => 'Không gian xung quanh trở nên mờ ảo và nóng nực, như thể những hạt hạ nguyên tử đang khiêu vũ trong vũ điệu của sự hủy diệt.',
                    'type' => 'REVEAL',
                    'confidence' => 0.99,
                ],
                $systemPrompt,
                $userPrompt,
                $start,
            );
        }

        return $this->withHistory(
            [
                'type' => 'IDLE',
                'description' => 'Một sự kiện chấn động đang diễn ra trong dòng chảy thực tại.',
                'payload' => ['thought' => 'Nothing to say.'],
                'confidence' => 0.5,
            ],
            $systemPrompt,
            $userPrompt,
            $start,
        );
    }

    private function withHistory(array $result, string $systemPrompt, string $userPrompt, float $startTime): array
    {
        $logger = $this->requestLogger ?? app(AIProviderRequestLogger::class);

        $logger->log([
            'provider' => 'fake-llm',
            'model' => 'deterministic-fake',
            'endpoint' => 'local://fake-llm',
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'request_payload' => (string) json_encode([
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response_payload' => (string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'http_status' => 200,
            'status' => 'SUCCESS',
            'error_message' => null,
            'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
        ]);

        return $result;
    }
}
