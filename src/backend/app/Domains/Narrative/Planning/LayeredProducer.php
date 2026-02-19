<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use App\Domains\Narrative\DTO\BeatSpec;
use App\Domains\Narrative\DTO\MemorySnapshot;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\Services\AI\AIAgentContext;
use App\Domains\Narrative\Serial\SerialGenrePreset;

/**
 * Multi-pass chapter generation: skeleton → full draft → polish.
 * Used when config use_layered_generation is true for higher quality.
 */
class LayeredProducer
{
    public function __construct(
        private readonly ?LLMProvider $llm = null
    ) {
    }

    /**
     * Produce chapter via 3 passes: skeleton, expand to prose, polish.
     *
     * @param array<string, mixed> $chronicleContext
     * @param string|array $styleInput genre_key or rich context
     * @return array{content: string, usage: array|null}
     */
    public function produce(
        BeatSpec $beatSpec,
        MemorySnapshot $memory,
        array $chronicleContext,
        string|array $styleInput,
        int $wordLimit = 600
    ): array {
        if ($this->llm === null) {
            return [
                'content' => '[LayeredProducer: no LLM] Chapter placeholder. Beat: ' . $beatSpec->emotion . '.',
                'usage' => null,
            ];
        }

        $hint = $this->systemHint($styleInput);
        $contextJson = json_encode($chronicleContext, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        // Pass 1: Skeleton (who appears, goal, conflict)
        $skeletonPrompt = "You are a plot planner. Output only a short bullet list: (1) characters present in this scene, (2) main goal of the scene, (3) central conflict or tension. No prose. Be concise.";
        $skeletonUser = "Arc: {$beatSpec->arcContext}. Beat: {$beatSpec->emotion}. Context: " . substr($contextJson, 0, 1500);
        $skeleton = $this->callLlm($skeletonPrompt, $skeletonUser);

        // Pass 2: Full draft from skeleton
        $draftPrompt = "You are a novelist. Write one chapter (~{$wordLimit} words) of a long-running serial. {$hint} Use this scene skeleton and expand into full prose. Respond with only the chapter text: no JSON, no numbering.";
        $draftUser = "Skeleton:\n{$skeleton}\n\nArc: {$beatSpec->arcContext}. Beat: {$beatSpec->emotion}.\n";
        if (!$memory->isEmpty()) {
            $draftUser .= "Memory: {$memory->digest}. Last paragraphs:\n{$memory->lastParagraphs}\n\n";
        }
        $draftUser .= "Context: " . substr($contextJson, 0, 2500);
        $draft = $this->callLlm($draftPrompt, $draftUser);

        // Pass 3: Polish (style, pacing; keep plot)
        $polishPrompt = "You are an editor. Rewrite the following chapter to improve prose, pacing, and sensory detail. Keep the plot and events unchanged. Output only the rewritten chapter text.";
        $polishUser = substr($draft, 0, 8000);
        $content = $this->callLlm($polishPrompt, $polishUser);

        return [
            'content' => $content !== '' ? $content : $draft,
            'usage' => null,
        ];
    }

    private function systemHint(string|array $styleInput): string
    {
        if (is_array($styleInput) && isset($styleInput['genre'])) {
            return 'Tone from world: genre=' . ($styleInput['genre'] ?? '') . '. ';
        }
        $key = is_string($styleInput) ? $styleInput : SerialGenrePreset::FANTASY_SCHOOL;
        $preset = SerialGenrePreset::get($key);
        return $preset['system_prompt_hint'] ?? 'Write one chapter. Maintain continuity.';
    }

    private function callLlm(string $systemPrompt, string $userPrompt): string
    {
        $response = app(AIAgentContext::class)->runWith('narrative.layered_producer', fn () => $this->llm->generate($systemPrompt, $userPrompt));
        if (isset($response['content']) && is_string($response['content'])) {
            return trim($response['content']);
        }
        if (isset($response['description']) && is_string($response['description'])) {
            return trim($response['description']);
        }
        if (isset($response['choices'][0]['message']['content'])) {
            return trim((string) $response['choices'][0]['message']['content']);
        }
        return '';
    }
}
