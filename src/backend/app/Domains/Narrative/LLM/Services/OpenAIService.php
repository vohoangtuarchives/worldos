<?php

namespace App\Domains\Narrative\LLM\Services;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\Domains\Narrative\LLM\Support\AIProviderRequestLogger;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService implements LLMProvider
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gpt-4-turbo-preview',
        protected ?AIProviderRequestLogger $requestLogger = null,
    ) {}

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $start = microtime(true);
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $requestBody = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ];

        $response = null;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($endpoint, $requestBody);

            if ($response->failed()) {
                throw new Exception('OpenAI Request Failed: ' . $response->body());
            }

            $content = $response->json('choices.0.message.content');

            Log::debug('OpenAI Raw Output: ' . $content);

            $data = json_decode((string) $content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON from OpenAI: ' . $content);
            }

            $this->logHistory(
                status: 'SUCCESS',
                systemPrompt: $systemPrompt,
                userPrompt: $userPrompt,
                requestBody: $requestBody,
                endpoint: $endpoint,
                response: $response,
                errorMessage: null,
                startTime: $start,
            );

            return $data;
        } catch (Exception $e) {
            $this->logHistory(
                status: 'FAILED',
                systemPrompt: $systemPrompt,
                userPrompt: $userPrompt,
                requestBody: $requestBody,
                endpoint: $endpoint,
                response: $response,
                errorMessage: $e->getMessage(),
                startTime: $start,
            );

            Log::error('LLM Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function logHistory(
        string $status,
        string $systemPrompt,
        string $userPrompt,
        array $requestBody,
        string $endpoint,
        ?Response $response,
        ?string $errorMessage,
        float $startTime,
    ): void {
        $logger = $this->requestLogger ?? app(AIProviderRequestLogger::class);

        $logger->log([
            'provider' => 'openai',
            'model' => $this->model,
            'endpoint' => $endpoint,
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'request_payload' => $this->encodePayload($requestBody),
            'response_payload' => $response ? $response->body() : null,
            'http_status' => $response?->status(),
            'status' => $status,
            'error_message' => $errorMessage,
            'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    protected function encodePayload(array $payload): string
    {
        return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
