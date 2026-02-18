<?php

declare(strict_types=1);

namespace App\Domains\Narrative\LLM\Services;

use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Unified LLM service: supports multiple platforms via config (OpenAI, Alibaba, etc.).
 * Uses config('llm.default') and config('llm.drivers.{driver}').
 */
class LLMService implements LLMProvider
{
    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $driver = config('llm.default', 'openai');
        $config = config("llm.drivers.{$driver}", []);

        if (empty($config['api_key'])) {
            throw new Exception("LLM driver [{$driver}] has no api_key. Set LLM_DRIVER and the driver's API key in .env.");
        }

        $baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
        $url = $baseUrl . '/chat/completions';
        $timeout = max(60, (int) ($config['timeout'] ?? 120));

        $payload = $this->buildPayload($config, $systemPrompt, $userPrompt);

        if (config('llm.log_requests', false)) {
            $this->logFullRequest($driver, $url, $payload);
        } else {
            Log::debug('LLM request', ['driver' => $driver, 'url' => $url, 'model' => $payload['model'] ?? null]);
        }

        $response = Http::withToken($config['api_key'])
            ->timeout($timeout)
            ->post($url, $payload);

        if ($response->failed()) {
            throw new Exception("LLM Request Failed [{$driver}]: " . $response->body());
        }

        $body = $response->json() ?? [];
        $rawContent = $body['choices'][0]['message']['content'] ?? $body['output'] ?? $body['result'] ?? null;

        if (config('llm.log_requests', false)) {
            $this->logFullResponse($driver, $rawContent, $body);
        } else {
            Log::debug('LLM Raw Output: ' . (is_string($rawContent) ? substr($rawContent, 0, 500) : json_encode($rawContent)));
        }

        $text = $this->extractChapterText($rawContent);
        $text = $this->stripThinkingAndKeepAnswer($text);

        if (!$this->looksLikeProse($text)) {
            $fallback = $this->findProseInResponse($body);
            if ($fallback !== '') {
                $text = $this->stripThinkingAndKeepAnswer($fallback);
            }
        }

        if (!$this->looksLikeProse($text)) {
            Log::warning('LLM invalid content. Driver: ' . $driver . ', keys: ' . json_encode(array_keys($body)));
            throw new Exception(
                'LLM trả về nội dung không hợp lệ. Thử: OPENAI_RESPONSE_FORMAT=0, đổi model, hoặc kiểm tra API key.'
            );
        }

        $usage = $this->extractUsage($body);
        return [
            'description' => $text,
            'content' => $text,
            'usage' => $usage,
        ];
    }

    /**
     * Extract token usage from API response (OpenAI/Alibaba compatible).
     *
     * @return array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int}
     */
    private function extractUsage(array $body): array
    {
        $usage = $body['usage'] ?? [];
        if (!is_array($usage)) {
            return [];
        }
        return array_filter([
            'prompt_tokens' => isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
            'completion_tokens' => isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
            'total_tokens' => isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null,
        ], static fn ($v) => $v !== null);
    }

    /**
     * Ghi đầy đủ request (system + user prompt, payload) ra log để debug / cải thiện prompt.
     */
    private function logFullRequest(string $driver, string $url, array $payload): void
    {
        $safe = $payload;
        unset($safe['messages']);
        $messages = $payload['messages'] ?? [];
        Log::channel('stack')->info('[LLM REQUEST] ' . $driver . ' ' . $url, [
            'payload_meta' => $safe,
            'system_prompt' => $messages[0]['content'] ?? '',
            'user_prompt' => $messages[1]['content'] ?? '',
            'system_prompt_length' => isset($messages[0]['content']) ? strlen($messages[0]['content']) : 0,
            'user_prompt_length' => isset($messages[1]['content']) ? strlen($messages[1]['content']) : 0,
        ]);
        Log::channel('stack')->info('[LLM REQUEST] --- SYSTEM PROMPT ---', [
            'content' => $messages[0]['content'] ?? '',
        ]);
        Log::channel('stack')->info('[LLM REQUEST] --- USER PROMPT ---', [
            'content' => $messages[1]['content'] ?? '',
        ]);
    }

    /**
     * Ghi response (raw content + summary) ra log khi debug.
     */
    private function logFullResponse(string $driver, mixed $rawContent, array $body): void
    {
        $preview = is_string($rawContent) ? substr($rawContent, 0, 2000) : json_encode($rawContent);
        Log::channel('stack')->info('[LLM RESPONSE] ' . $driver, [
            'raw_preview' => $preview,
            'raw_length' => is_string($rawContent) ? strlen($rawContent) : 0,
            'usage' => $body['usage'] ?? null,
        ]);
    }

    private function buildPayload(array $config, string $systemPrompt, string $userPrompt): array
    {
        $payload = [
            'model' => $config['model'] ?? 'gpt-4-turbo-preview',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
        ];

        if (!empty($config['response_format'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        if (!empty($config['extra_body']) && is_array($config['extra_body'])) {
            $payload = array_merge($payload, $config['extra_body']);
        }

        return $payload;
    }

    private function extractChapterText(mixed $rawContent): string
    {
        if (is_string($rawContent)) {
            $trimmed = trim($rawContent);
            if ($trimmed === '') {
                return '';
            }
            $data = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                if (isset($data['content']) && is_string($data['content'])) {
                    return trim($data['content']);
                }
                if (isset($data['description']) && is_string($data['description'])) {
                    return trim($data['description']);
                }
                foreach ($data as $v) {
                    if (is_string($v) && strlen($v) > 20) {
                        return trim($v);
                    }
                }
            }
            return $trimmed;
        }

        if (is_array($rawContent)) {
            if (isset($rawContent['content']) && is_string($rawContent['content'])) {
                return trim($rawContent['content']);
            }
            if (isset($rawContent['description']) && is_string($rawContent['description'])) {
                return trim($rawContent['description']);
            }
        }

        if (is_numeric($rawContent)) {
            return '';
        }

        return trim((string) $rawContent);
    }

    private function stripThinkingAndKeepAnswer(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        if (preg_match('/<\/think\s*>/iu', $text)) {
            $afterThink = trim((string) preg_replace('/^.*?<\/think\s*>\s*/ius', '', $text));
            if ($afterThink !== '' && strlen($afterThink) > 20) {
                return $afterThink;
            }
        }

        if (preg_match('/<\/reasoning\s*>/iu', $text)) {
            $after = trim((string) preg_replace('/^.*?<\/reasoning\s*>\s*/ius', '', $text));
            if ($after !== '' && strlen($after) > 20) {
                return $after;
            }
        }

        foreach (['Trả lời:', 'Answer:', 'Response:', 'Nội dung chương:', 'Chapter:', "\n---\n"] as $marker) {
            $pos = mb_stripos($text, $marker);
            if ($pos !== false && $pos < mb_strlen($text) - 30) {
                $after = trim(mb_substr($text, $pos + mb_strlen($marker)));
                if ($after !== '' && strlen($after) > 30 && preg_match('/[\p{L}]/u', $after)) {
                    return $after;
                }
            }
        }

        return $text;
    }

    private function looksLikeProse(string $text): bool
    {
        if ($text === '' || strlen($text) < 50) {
            return false;
        }
        $letters = preg_match_all('/[\p{L}]/u', $text);
        $digits = preg_match_all('/[0-9]/', $text);
        $total = strlen($text);
        if ($total === 0) {
            return false;
        }
        if ($letters < 20) {
            return false;
        }
        if ($digits > $total * 0.5) {
            return false;
        }
        return true;
    }

    private function findProseInResponse(array $body): string
    {
        $candidates = [];
        $this->collectStrings($body, $candidates);
        foreach ($candidates as $s) {
            if ($this->looksLikeProse($s)) {
                return $s;
            }
        }
        return '';
    }

    private function collectStrings(mixed $node, array &$out, int $depth = 0): void
    {
        if ($depth > 10) {
            return;
        }
        if (is_string($node)) {
            if (strlen(trim($node)) >= 50) {
                $out[] = trim($node);
            }
            return;
        }
        if (is_array($node)) {
            foreach ($node as $v) {
                $this->collectStrings($v, $out, $depth + 1);
            }
        }
    }
}
