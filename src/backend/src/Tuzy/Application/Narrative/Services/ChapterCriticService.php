<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Critique a chapter draft: coherence, pacing, character consistency, genre fit.
 * Returns score 0-1 and short feedback. Used to decide whether to regenerate.
 */
class ChapterCriticService
{
    public function __construct(
        private readonly float $threshold = 0.5,
        private readonly int $maxRetries = 2
    ) {
    }

    /**
     * Score chapter and return feedback.
     *
     * @return array{score: float, feedback: string, pass: bool}
     */
    public function critique(string $chapterContent, string $genreKey = 'wuxia'): array
    {
        $driver = config('llm.drivers.' . config('llm.default'), config('llm.drivers.openai'));
        $apiKey = $driver['api_key'] ?? '';
        $baseUrl = rtrim($driver['base_url'] ?? 'https://api.openai.com/v1', '/');
        $model = $driver['model'] ?? 'gpt-4-turbo-preview';
        $timeout = (int) ($driver['timeout'] ?? 60);

        if ($apiKey === '') {
            return ['score' => 1.0, 'feedback' => 'Critic skipped (no API key).', 'pass' => true];
        }

        $systemPrompt = <<<PROMPT
You are a fiction editor. Output ONLY a valid JSON object with keys:
- "score": number 0 to 1 (1 = excellent).
- "feedback": string (1-2 sentences: what works, what to improve).
Rate: coherence, pacing, character consistency, genre fit ({$genreKey}). No other keys. Valid JSON only.
PROMPT;

        $userPrompt = "Chapter excerpt (first ~1500 chars):\n" . substr($chapterContent, 0, 1500) . "\n\nOutput JSON only.";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $response = Http::withToken($apiKey)->timeout($timeout)->post($baseUrl . '/chat/completions', $payload);
            if ($response->failed()) {
                Log::warning('ChapterCriticService request failed', ['body' => $response->body()]);
                return ['score' => 1.0, 'feedback' => 'Critic request failed.', 'pass' => true];
            }
            $body = $response->json();
            $raw = $body['choices'][0]['message']['content'] ?? $body['output'] ?? $body['result'] ?? null;
            if (!is_string($raw)) {
                return ['score' => 1.0, 'feedback' => '', 'pass' => true];
            }
            $data = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return ['score' => 1.0, 'feedback' => '', 'pass' => true];
            }
            $score = isset($data['score']) ? (float) $data['score'] : 0.5;
            $score = max(0.0, min(1.0, $score));
            $feedback = isset($data['feedback']) && is_string($data['feedback']) ? $data['feedback'] : '';
            return [
                'score' => $score,
                'feedback' => $feedback,
                'pass' => $score >= $this->threshold,
            ];
        } catch (\Throwable $e) {
            Log::warning('ChapterCriticService error: ' . $e->getMessage());
            return ['score' => 1.0, 'feedback' => '', 'pass' => true];
        }
    }

    public function getThreshold(): float
    {
        return $this->threshold;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }
}
