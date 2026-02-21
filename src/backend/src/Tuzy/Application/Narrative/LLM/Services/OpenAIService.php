<?php

namespace Tuzy\Application\Narrative\LLM\Services;

use Tuzy\Application\Narrative\LLM\Contracts\LLMProvider;
use Tuzy\Application\Narrative\LLM\Support\AIProviderRequestLogger;
use App\Services\AI\AIAgentContext;
use App\Services\AI\AIFeatureAgentResolver;
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
        protected ?AIAgentContext $agentContext = null,
        protected ?AIFeatureAgentResolver $agentResolver = null,
    ) {}

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $start = microtime(true);
        $featureKey = ($this->agentContext ?? app(AIAgentContext::class))->get();
        $agentConfig = ($this->agentResolver ?? app(AIFeatureAgentResolver::class))->resolve($featureKey);

        $resolvedModel = $agentConfig['model'] ?: $this->model;
        $resolvedSystemPrompt = $agentConfig['system_prompt'] ?: $systemPrompt;
        $resolvedOptions = $agentConfig['options'] ?? [];

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $requestBody = [
            'model' => $resolvedModel,
            'messages' => [
                ['role' => 'system', 'content' => $resolvedSystemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => $resolvedOptions['temperature'] ?? 0.7,
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
                systemPrompt: $resolvedSystemPrompt,
                userPrompt: $userPrompt,
                requestBody: $requestBody,
                endpoint: $endpoint,
                response: $response,
                errorMessage: null,
                startTime: $start,
                featureKey: $agentConfig['feature_key'] ?? $featureKey,
                agentName: $agentConfig['agent_name'] ?? null,
                model: $resolvedModel,
            );

            return $data;
        } catch (Exception $e) {
            $this->logHistory(
                status: 'FAILED',
                systemPrompt: $resolvedSystemPrompt,
                userPrompt: $userPrompt,
                requestBody: $requestBody,
                endpoint: $endpoint,
                response: $response,
                errorMessage: $e->getMessage(),
                startTime: $start,
                featureKey: $agentConfig['feature_key'] ?? $featureKey,
                agentName: $agentConfig['agent_name'] ?? null,
                model: $resolvedModel,
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
        ?string $featureKey,
        ?string $agentName,
        ?string $model,
    ): void {
        $logger = $this->requestLogger ?? app(AIProviderRequestLogger::class);

        $logger->log([
            'provider' => 'openai',
            'model' => $model,
            'endpoint' => $endpoint,
            'feature_key' => $featureKey,
            'agent_name' => $agentName,
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
