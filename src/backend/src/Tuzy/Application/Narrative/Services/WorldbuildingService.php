<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Services;

use App\Models\NarrativeSeries;
use App\Models\StoryBible;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generate Story Bible (synopsis, style_notes, worldbuilding_rules) from short premise + genre.
 * Calls LLM once; saves to StoryBible. Used by POST /serial/series/{id}/story-bible/generate-from-premise.
 */
class WorldbuildingService
{
    public function generateFromPremise(string $seriesId, string $premise, string $genreKey = 'wuxia'): StoryBible
    {
        $series = NarrativeSeries::findOrFail($seriesId);
        $driver = config('llm.drivers.' . config('llm.default'), config('llm.drivers.openai'));
        $apiKey = $driver['api_key'] ?? '';
        $baseUrl = rtrim($driver['base_url'] ?? 'https://api.openai.com/v1', '/');
        $model = $driver['model'] ?? 'gpt-4-turbo-preview';
        $timeout = (int) ($driver['timeout'] ?? 120);

        if ($apiKey === '') {
            throw new \RuntimeException('LLM API key not configured. Set OPENAI_API_KEY or LLM_API_KEY.');
        }

        $systemPrompt = <<<PROMPT
You are a story architect. Output ONLY a valid JSON object with no markdown or explanation.
Required keys:
- "synopsis": string (2-4 paragraphs summarizing the series premise, world, and main conflict).
- "style_notes": string (tone, prose style, e.g. "wuxia classic", "sensory detail").
- "worldbuilding_rules": object with optional keys:
  - "power_system": object or string (name, tiers/levels, taboos e.g. "tẩu hỏa nhập ma").
  - "factions": array of { "name", "relation", "traits" } (sects, clans, powers).
  - "locations": array of { "name", "traits" } (key places).
  - "timeline": string or array (eras, major events).
Strictly follow the genre and premise. No other keys. Valid JSON only.
PROMPT;

        $userPrompt = sprintf(
            "Genre: %s. Series title: %s.\n\nPremise:\n%s\n\nOutput the JSON object only.",
            $genreKey,
            $series->title ?? 'Untitled',
            $premise
        );

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.5,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->post($baseUrl . '/chat/completions', $payload);

        if ($response->failed()) {
            Log::error('WorldbuildingService LLM request failed', ['body' => $response->body()]);
            throw new \RuntimeException('Worldbuilding LLM request failed: ' . $response->body());
        }

        $body = $response->json();
        $raw = $body['choices'][0]['message']['content'] ?? $body['output'] ?? $body['result'] ?? null;
        if (!is_string($raw)) {
            throw new \RuntimeException('Worldbuilding LLM returned no content.');
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new \RuntimeException('Worldbuilding LLM did not return valid JSON.');
        }

        $synopsis = isset($data['synopsis']) && is_string($data['synopsis']) ? $data['synopsis'] : '';
        $styleNotes = isset($data['style_notes']) && is_string($data['style_notes']) ? $data['style_notes'] : '';
        $worldbuildingRules = isset($data['worldbuilding_rules']) && is_array($data['worldbuilding_rules'])
            ? $data['worldbuilding_rules']
            : [];

        $bible = StoryBible::firstOrCreate(
            ['narrative_series_id' => $seriesId],
            ['braindump' => $premise, 'synopsis' => null, 'style_notes' => null, 'worldbuilding_rules' => null]
        );
        $bible->update([
            'braindump' => $bible->braindump ?? $premise,
            'synopsis' => $synopsis !== '' ? $synopsis : $bible->synopsis,
            'style_notes' => $styleNotes !== '' ? $styleNotes : $bible->style_notes,
            'worldbuilding_rules' => $worldbuildingRules !== [] ? $worldbuildingRules : $bible->worldbuilding_rules,
        ]);

        return $bible;
    }
}
