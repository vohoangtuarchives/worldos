<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge\Extractor;

use App\Domains\Narrative\Bridge\Contracts\StoryEventExtractorInterface;
use App\Domains\Narrative\Bridge\DTO\StoryEvent;

/**
 * Rule-based extraction: keywords/phrases to event type + severity.
 * No LLM; cheap and deterministic.
 */
final class RuleBasedStoryEventExtractor implements StoryEventExtractorInterface
{
    public function extract(string $chapterContent): array
    {
        $text = strip_tags($chapterContent);
        $text = mb_strtolower($text);
        $events = [];
        $seen = [];

        foreach ($this->patterns() as $pattern => $config) {
            if (stripos($text, $pattern) !== false) {
                $key = $config['type'] . ':' . $pattern;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $events[] = new StoryEvent(
                    type: $config['type'],
                    severity: $config['severity'],
                    location: null,
                    symbol: null,
                    actor: null
                );
            }
        }

        return $events;
    }

    /** @return array<string, array{type: string, severity: float}> */
    private function patterns(): array
    {
        return [
            'dark magic' => ['type' => 'magic_corruption', 'severity' => 0.35],
            'shadow mark' => ['type' => 'magic_corruption', 'severity' => 0.4],
            'corruption' => ['type' => 'magic_corruption', 'severity' => 0.3],
            'magic collapsed' => ['type' => 'magic_collapse', 'severity' => 0.5],
            'magic collapse' => ['type' => 'magic_collapse', 'severity' => 0.45],
            'invasion' => ['type' => 'invasion', 'severity' => 0.5],
            'invaded' => ['type' => 'invasion', 'severity' => 0.4],
            'dark lord' => ['type' => 'threat_rise', 'severity' => 0.4],
            'death eater' => ['type' => 'threat_rise', 'severity' => 0.35],
            'death eaters' => ['type' => 'threat_rise', 'severity' => 0.35],
            'betrayal' => ['type' => 'relationship_fracture', 'severity' => 0.35],
            'betrayed' => ['type' => 'relationship_fracture', 'severity' => 0.4],
            'attack' => ['type' => 'violence', 'severity' => 0.3],
            'attacked' => ['type' => 'violence', 'severity' => 0.35],
            'killed' => ['type' => 'violence', 'severity' => 0.5],
            'murder' => ['type' => 'violence', 'severity' => 0.5],
        ];
    }
}
