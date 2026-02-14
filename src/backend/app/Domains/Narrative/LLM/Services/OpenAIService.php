<?php

namespace App\Domains\Narrative\LLM\Services;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService implements LLMProvider
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gpt-4-turbo-preview'
    ) {}

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'response_format' => ['type' => 'json_object'], // Force JSON
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                throw new Exception("OpenAI Request Failed: " . $response->body());
            }

            $content = $response->json('choices.0.message.content');
            
            // Log raw output for debugging
            Log::debug("OpenAI Raw Output: " . $content);

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON from OpenAI: " . $content);
            }

            return $data;

        } catch (Exception $e) {
            Log::error("LLM Generation Error: " . $e->getMessage());
            // Fallback or rethrow? For now rethrow to let engine handle.
            throw $e;
        }
    }
}
