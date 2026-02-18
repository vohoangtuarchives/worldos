<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use App\Domains\Narrative\DTO\BeatSpec;
use App\Domains\Narrative\DTO\MemorySnapshot;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\Domains\Narrative\Serial\SerialGenrePreset;

/**
 * Phase 5.3: Constrained LLM chapter production from blueprint (POV, tone, word limit).
 * Supports one-off chapters and serial long-form (story_so_far + genre preset).
 * New path: BeatSpec + MemorySnapshot for compressed prompt (Planner/Generator separation).
 */
class ChapterProducer
{
    public function __construct(
        private readonly ?LLMProvider $llm = null
    ) {
    }

    /**
     * Produce a chapter draft from blueprint and optional chronicle context.
     *
     * @param array{chapter_index: int, emotional_objective: string, conflict_delta: array, motif_targets: array} $blueprint
     * @param array<string, mixed> $chronicleContext
     */
    public function produce(array $blueprint, array $chronicleContext = []): string
    {
        $objective = $blueprint['emotional_objective'] ?? 'tension';
        $systemPrompt = 'You are a narrative writer. Write one short chapter. Max 300 words. No plot beyond the blueprint.';
        $userPrompt = sprintf(
            "Emotional objective: %s. Context: %s",
            $objective,
            json_encode($chronicleContext, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)
        );
        return $this->callLlm($systemPrompt, $userPrompt, $blueprint, $objective);
    }

    /**
     * Produce one chapter for a long-form serial (Harry Potter / Tiếu Ngạo Giang Hồ style).
     * Uses story_so_far for continuity. Tone: từ preset (genre_key) hoặc emergent (richContext từ world).
     *
     * @param array{chapter_index: int, emotional_objective: string, conflict_delta?: array, motif_targets?: array, arc_context?: string} $blueprint
     * @param array<string, mixed> $chronicleContext
     * @param string $storySoFar Summary of previous chapters (or empty for first chapter)
     * @param string|array{genre: string, traits: array, situations?: array, intro_phrase?: string} $genreKeyOrRichContext SerialGenrePreset key (string) hoặc rich context (array) khi emergent
     * @param int $wordLimit Approximate words per chapter (default 600 for serial)
     */
    public function produceSerialChapter(
        array $blueprint,
        array $chronicleContext,
        string $storySoFar = '',
        string|array $genreKeyOrRichContext = SerialGenrePreset::FANTASY_SCHOOL,
        int $wordLimit = 600
    ): string {
        $systemHint = $this->buildSerialSystemHint($genreKeyOrRichContext);
        $objective = $blueprint['emotional_objective'] ?? 'rising';
        $arcContext = $blueprint['arc_context'] ?? '';

        $systemPrompt = "You are a novelist writing a long-running serial. {$systemHint} This chapter: about {$wordLimit} words. Stay in character and maintain plot continuity. Respond with only the chapter prose (narrative text): no JSON, no code, no numbers, no thinking or reasoning block — only the story text.";

        $parts = [
            "Emotional beat for this chapter: {$objective}.",
            "Arc context: {$arcContext}.",
        ];
        if (!empty($chronicleContext['synopsis'])) {
            $parts[] = 'Story synopsis: ' . trim((string) $chronicleContext['synopsis']);
        }
        if (!empty($chronicleContext['story_bible_characters']) && is_array($chronicleContext['story_bible_characters'])) {
            $charLines = [];
            foreach (array_slice($chronicleContext['story_bible_characters'], 0, 10) as $c) {
                $name = $c['name'] ?? '?';
                $role = $c['role'] ?? '';
                $traits = $c['traits'] ?? '';
                $charLines[] = '- ' . $name . ($role !== '' ? " ({$role})" : '') . ($traits !== '' ? ": {$traits}" : '');
            }
            $parts[] = 'Characters: ' . implode("\n", $charLines);
        }
        if (!empty($chronicleContext['worldbuilding_rules']) && is_array($chronicleContext['worldbuilding_rules'])) {
            $parts[] = 'Worldbuilding rules (obey strictly): ' . json_encode($chronicleContext['worldbuilding_rules'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        if (!empty($chronicleContext['lorebook_locations']) && is_array($chronicleContext['lorebook_locations'])) {
            $parts[] = 'Relevant locations (use when fitting): ' . json_encode($chronicleContext['lorebook_locations'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        if (!empty($chronicleContext['lorebook_factions']) && is_array($chronicleContext['lorebook_factions'])) {
            $parts[] = 'Relevant factions (use when fitting): ' . json_encode($chronicleContext['lorebook_factions'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        $parts[] = "World/simulation context: " . json_encode($chronicleContext, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        if ($storySoFar !== '') {
            $parts[] = "Story so far (summarize and continue): ---\n{$storySoFar}\n---";
        } else {
            $parts[] = "This is the first chapter of this arc. Establish setting and protagonist.";
        }

        $userPrompt = implode("\n\n", $parts);
        return $this->callLlm($systemPrompt, $userPrompt, $blueprint, $objective);
    }

    /**
     * Produce one chapter from structured BeatSpec + MemorySnapshot (compressed prompt, no raw story_so_far).
     * Returns content and usage for telemetry.
     *
     * @param array<string, mixed> $chronicleContext Series title, arc, config, optional world_state
     * @param string|array{genre: string, traits: array, situations?: array} $genreKeyOrRichContext
     * @return array{content: string, usage: array|null}
     */
    public function produceSerialChapterFromSpec(
        BeatSpec $beatSpec,
        MemorySnapshot $memory,
        array $chronicleContext,
        string|array $genreKeyOrRichContext = SerialGenrePreset::FANTASY_SCHOOL,
        int $wordLimit = 600
    ): array {
        $systemHint = $this->buildSerialSystemHint($genreKeyOrRichContext);
        $systemPrompt = "You are a novelist writing a long-running serial. {$systemHint} This chapter: about {$wordLimit} words. Stay in character and maintain plot continuity. Respond with only the chapter prose (narrative text): no JSON, no code, no numbers, no thinking or reasoning block — only the story text.";

        $parts = [
            'Beat: ' . $beatSpec->emotion . '.',
            'Tension: ' . round($beatSpec->tension, 2) . '.',
            'Arc: ' . $beatSpec->arcContext . '.',
        ];
        if (!empty($chronicleContext['synopsis'])) {
            $parts[] = 'Story synopsis: ' . trim((string) $chronicleContext['synopsis']);
        }
        if (!empty($chronicleContext['story_bible_characters']) && is_array($chronicleContext['story_bible_characters'])) {
            $charLines = [];
            foreach (array_slice($chronicleContext['story_bible_characters'], 0, 10) as $c) {
                $name = $c['name'] ?? '?';
                $role = $c['role'] ?? '';
                $traits = $c['traits'] ?? '';
                $charLines[] = '- ' . $name . ($role !== '' ? " ({$role})" : '') . ($traits !== '' ? ": {$traits}" : '');
            }
            $parts[] = 'Characters: ' . implode("\n", $charLines);
        }
        if (!empty($chronicleContext['worldbuilding_rules']) && is_array($chronicleContext['worldbuilding_rules'])) {
            $parts[] = 'Worldbuilding rules (obey strictly): ' . json_encode($chronicleContext['worldbuilding_rules'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        if (!empty($chronicleContext['lorebook_locations']) && is_array($chronicleContext['lorebook_locations'])) {
            $parts[] = 'Relevant locations (use when fitting): ' . json_encode($chronicleContext['lorebook_locations'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        if (!empty($chronicleContext['lorebook_factions']) && is_array($chronicleContext['lorebook_factions'])) {
            $parts[] = 'Relevant factions (use when fitting): ' . json_encode($chronicleContext['lorebook_factions'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }
        if (!empty($chronicleContext['current_world_state_narrative'])) {
            $parts[] = $chronicleContext['current_world_state_narrative'];
        }
        $parts[] = 'World/simulation context: ' . json_encode($chronicleContext, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        if (!$memory->isEmpty()) {
            $parts[] = 'Memory: ' . $memory->digest . '.';
            if ($memory->lastParagraphs !== '') {
                $parts[] = "Last paragraphs:\n" . $memory->lastParagraphs;
            }
            $parts[] = 'Write ~' . $wordLimit . ' words, prose only. Continue from the last paragraphs.';
        } else {
            $parts[] = 'This is the first chapter of this arc. Establish setting and protagonist. Write ~' . $wordLimit . ' words, prose only.';
        }

        $userPrompt = implode("\n\n", $parts);
        $blueprint = $beatSpec->toBlueprintFragment();
        return $this->callLlmWithUsage($systemPrompt, $userPrompt, $blueprint, $beatSpec->emotion);
    }

    /**
     * Build system hint from preset (string genre_key) or emergent rich context (array).
     */
    private function buildSerialSystemHint(string|array $genreKeyOrRichContext): string
    {
        if (is_array($genreKeyOrRichContext) && isset($genreKeyOrRichContext['genre'])) {
            $genre = $genreKeyOrRichContext['genre'];
            $traits = $genreKeyOrRichContext['traits'] ?? [];
            $traitsStr = is_array($traits) ? implode(', ', $traits) : (string) $traits;
            $situations = $genreKeyOrRichContext['situations'] ?? [];
            $situationPhrases = [];
            foreach (is_array($situations) ? $situations : [] as $s) {
                if (isset($s['phrase'])) {
                    $situationPhrases[] = $s['phrase'];
                }
            }
            $hint = "Tone and genre emerge from the world: genre={$genre}, traits: {$traitsStr}. ";
            if ($situationPhrases !== []) {
                $hint .= 'Current situation themes: ' . implode('; ', array_slice($situationPhrases, 0, 3)) . '. ';
            }
            return $hint . 'Write one chapter. Maintain continuity with previous chapters. Vietnamese or English as requested.';
        }

        $genreKey = is_string($genreKeyOrRichContext) ? $genreKeyOrRichContext : SerialGenrePreset::FANTASY_SCHOOL;
        $preset = SerialGenrePreset::get($genreKey);
        return $preset['system_prompt_hint'] ?? 'Write one chapter of a long-form serial. Maintain continuity.';
    }

    private function callLlm(string $systemPrompt, string $userPrompt, array $blueprint, string $objective): string
    {
        if ($this->llm !== null) {
            $response = $this->llm->generate($systemPrompt, $userPrompt);
            if (isset($response['content']) && is_string($response['content'])) {
                return $response['content'];
            }
            if (isset($response['description']) && is_string($response['description'])) {
                return $response['description'];
            }
            if (isset($response['choices'][0]['message']['content'])) {
                return (string) $response['choices'][0]['message']['content'];
            }
            return (string) json_encode($response);
        }
        return "[Chapter " . (($blueprint['chapter_index'] ?? 0) + 1) . " placeholder — emotional objective: {$objective}]";
    }

    /**
     * Call LLM and return content + usage for telemetry.
     *
     * @return array{content: string, usage: array|null}
     */
    private function callLlmWithUsage(string $systemPrompt, string $userPrompt, array $blueprint, string $objective): array
    {
        if ($this->llm !== null) {
            $response = $this->llm->generate($systemPrompt, $userPrompt);
            $content = null;
            if (isset($response['content']) && is_string($response['content'])) {
                $content = $response['content'];
            } elseif (isset($response['description']) && is_string($response['description'])) {
                $content = $response['description'];
            } elseif (isset($response['choices'][0]['message']['content'])) {
                $content = (string) $response['choices'][0]['message']['content'];
            } else {
                $content = (string) json_encode($response);
            }
            $usage = $response['usage'] ?? null;
            if (is_array($usage)) {
                return ['content' => $content, 'usage' => $usage];
            }
            return ['content' => $content, 'usage' => null];
        }
        return [
            'content' => "[Chapter " . (($blueprint['chapter_index'] ?? 0) + 1) . " placeholder — emotional objective: {$objective}]",
            'usage' => null,
        ];
    }
}
