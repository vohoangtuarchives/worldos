<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Services;

use App\Models\NarrativeArcOutline;
use App\Models\NarrativeSeries;
use App\Models\StoryBible;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generate outline (saga → season → arc) from Story Bible + genre. Saves to narrative_arc_outlines.
 */
class PlotPlannerService
{
    public function generateOutline(string $seriesId, int $booksCount = 7): array
    {
        $series = NarrativeSeries::with('storyBible.activeCharacters')->findOrFail($seriesId);
        $bible = $series->storyBible;
        $synopsis = $bible?->synopsis ?? $series->title ?? 'Untitled series';
        $genreKey = $series->genre_key ?? 'wuxia';
        $driver = config('llm.drivers.' . config('llm.default'), config('llm.drivers.openai'));
        $apiKey = $driver['api_key'] ?? '';
        $baseUrl = rtrim($driver['base_url'] ?? 'https://api.openai.com/v1', '/');
        $model = $driver['model'] ?? 'gpt-4-turbo-preview';
        $timeout = (int) ($driver['timeout'] ?? 120);

        if ($apiKey === '') {
            throw new \RuntimeException('LLM API key not configured.');
        }

        $systemPrompt = <<<PROMPT
You are a plot architect. Output ONLY a valid JSON object.
Keys: "saga_one_line" (string), "seasons" (array of strings, one-line per season), "arcs" (array of objects: { "title", "one_line" }).
Arc count must be exactly {$booksCount}. Seasons can be fewer (e.g. 2-3). No other keys. Valid JSON only.
PROMPT;

        $userPrompt = "Genre: {$genreKey}. Synopsis: " . substr($synopsis, 0, 2000) . "\n\nGenerate saga one-line, then seasons, then {$booksCount} arc one-lines. JSON only.";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.5,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::withToken($apiKey)->timeout($timeout)->post($baseUrl . '/chat/completions', $payload);
        if ($response->failed()) {
            Log::error('PlotPlannerService LLM failed', ['body' => $response->body()]);
            throw new \RuntimeException('Plot planner LLM request failed.');
        }

        $body = $response->json();
        $raw = $body['choices'][0]['message']['content'] ?? $body['output'] ?? $body['result'] ?? null;
        if (!is_string($raw)) {
            throw new \RuntimeException('Plot planner returned no content.');
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new \RuntimeException('Plot planner did not return valid JSON.');
        }

        NarrativeArcOutline::where('narrative_series_id', $seriesId)->delete();

        $sagaLine = $data['saga_one_line'] ?? '';
        if ($sagaLine !== '') {
            NarrativeArcOutline::create([
                'narrative_series_id' => $seriesId,
                'level' => NarrativeArcOutline::LEVEL_SAGA,
                'index' => 0,
                'one_line' => $sagaLine,
                'status' => NarrativeArcOutline::STATUS_DRAFT,
            ]);
        }

        $seasons = $data['seasons'] ?? [];
        foreach (array_values($seasons) as $i => $oneLine) {
            if (is_string($oneLine) && $oneLine !== '') {
                NarrativeArcOutline::create([
                    'narrative_series_id' => $seriesId,
                    'level' => NarrativeArcOutline::LEVEL_SEASON,
                    'index' => $i,
                    'one_line' => $oneLine,
                    'status' => NarrativeArcOutline::STATUS_DRAFT,
                ]);
            }
        }

        $arcs = $data['arcs'] ?? [];
        $created = [];
        foreach (array_values($arcs) as $i => $arc) {
            $title = is_array($arc) ? ($arc['title'] ?? 'Tập ' . ($i + 1)) : 'Tập ' . ($i + 1);
            $oneLine = is_array($arc) ? ($arc['one_line'] ?? '') : (string) $arc;
            if ($oneLine === '' && is_string($arc)) {
                $oneLine = $arc;
            }
            $created[] = NarrativeArcOutline::create([
                'narrative_series_id' => $seriesId,
                'level' => NarrativeArcOutline::LEVEL_ARC,
                'index' => $i,
                'title' => $title,
                'one_line' => $oneLine !== '' ? $oneLine : 'Arc ' . ($i + 1),
                'status' => NarrativeArcOutline::STATUS_DRAFT,
            ]);
        }

        return $created;
    }
}
