<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Services;

use App\Models\StoryBible;
use App\Models\SerialChapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Phase 4: After chapter generation, extract facts from chapter and compare with story bible canon.
 * Log conflicts and flag chapter needs_review with consistency_notes.
 */
class ConsistencyValidator
{
    public function __construct(
        private readonly float $minScoreToSkipFlag = 0.7
    ) {
    }

    /**
     * Validate chapter content against story bible (characters, locations, power rules).
     * Returns report: has_conflicts, notes (list of strings), score 0-1.
     * Caller should set chapter.needs_review and consistency_notes when has_conflicts.
     *
     * @return array{has_conflicts: bool, notes: list<string>, score: float}
     */
    public function validate(string $seriesId, string $chapterContent): array
    {
        $bible = StoryBible::where('narrative_series_id', $seriesId)->with('activeCharacters')->first();
        if ($bible === null) {
            return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
        }

        $canon = $this->buildCanonSummary($bible);
        if ($canon === '') {
            return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
        }

        $driver = config('llm.drivers.' . config('llm.default'), config('llm.drivers.openai'));
        $apiKey = $driver['api_key'] ?? '';
        $baseUrl = rtrim($driver['base_url'] ?? 'https://api.openai.com/v1', '/');
        $model = $driver['model'] ?? 'gpt-4-turbo-preview';
        $timeout = (int) ($driver['timeout'] ?? 60);

        if ($apiKey === '') {
            return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
        }

        $systemPrompt = <<<PROMPT
You are a continuity checker. Given a story canon (characters, locations, power rules) and a chapter excerpt, output ONLY a valid JSON object with:
- "score": number 0 to 1 (1 = no conflicts with canon).
- "conflicts": array of strings, each one sentence describing a contradiction (e.g. wrong age, wrong location, power used that doesn't exist). If none, use [].
No other keys. Valid JSON only.
PROMPT;

        $userPrompt = "Canon:\n" . substr($canon, 0, 2000) . "\n\nChapter excerpt:\n" . substr($chapterContent, 0, 4000) . "\n\nOutput JSON only.";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $response = Http::withToken($apiKey)->timeout($timeout)->post($baseUrl . '/chat/completions', $payload);
            if ($response->failed()) {
                Log::warning('ConsistencyValidator request failed', ['body' => $response->body()]);
                return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
            }
            $body = $response->json();
            $raw = $body['choices'][0]['message']['content'] ?? $body['output'] ?? $body['result'] ?? null;
            if (!is_string($raw)) {
                return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
            }
            $data = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
            }
            $score = isset($data['score']) ? (float) $data['score'] : 1.0;
            $score = max(0.0, min(1.0, $score));
            $conflicts = isset($data['conflicts']) && is_array($data['conflicts'])
                ? array_values(array_map('strval', $data['conflicts']))
                : [];
            $hasConflicts = $score < $this->minScoreToSkipFlag && count($conflicts) > 0;
            return [
                'has_conflicts' => $hasConflicts,
                'notes' => $conflicts,
                'score' => $score,
            ];
        } catch (\Throwable $e) {
            Log::warning('ConsistencyValidator error: ' . $e->getMessage());
            return ['has_conflicts' => false, 'notes' => [], 'score' => 1.0];
        }
    }

    private function buildCanonSummary(StoryBible $bible): string
    {
        $parts = [];
        if ($bible->synopsis !== null && $bible->synopsis !== '') {
            $parts[] = 'Synopsis: ' . $bible->synopsis;
        }
        $rules = $bible->worldbuilding_rules;
        if (is_array($rules) && $rules !== []) {
            $parts[] = 'Worldbuilding: ' . json_encode($rules, JSON_UNESCAPED_UNICODE);
        }
        foreach ($bible->activeCharacters as $c) {
            $parts[] = 'Character: ' . $c->name . ' - ' . ($c->role ?? '') . '; ' . (is_array($c->traits) ? implode(', ', $c->traits) : (string) ($c->traits ?? ''));
        }
        return implode("\n", $parts);
    }
}
