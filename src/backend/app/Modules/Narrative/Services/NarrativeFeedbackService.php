<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Services;

use App\WorldOS\CivilizationMemory\Contracts\MythRepositoryInterface;
use App\WorldOS\CivilizationMemory\Entities\WorldMythEntity;
use App\WorldOS\CivilizationMemory\ValueObjects\MythId;
use App\WorldOS\CivilizationMemory\ValueObjects\MythStrength;
use App\Modules\Narrative\Entities\SerialChapterEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Narrative Feedback Service — closes the IP Factory loop.
 *
 * From docs §11.1 Phase 4: Canonize → WorldMyth → InfluencePipeline → Simulation.
 *
 * When a chapter is canonized, its key narrative elements
 * become WorldMyths that feed back into the simulation.
 */
final class NarrativeFeedbackService
{
    public function __construct(
        private readonly MythRepositoryInterface $mythRepository,
    ) {
    }

    /**
     * Process a canonized chapter: extract key narratives → create WorldMyths.
     */
    public function processCanonization(
        SerialChapterEntity $chapter,
        UniverseId $universeId,
        int $currentTick,
    ): array {
        $myths = [];

        // Extract narrative elements from chapter text
        $elements = $this->extractNarrativeElements($chapter->getRawText());

        foreach ($elements as $element) {
            $myth = WorldMythEntity::emerge(
                universeId: $universeId,
                name: $element['name'],
                description: $element['description'],
                originTick: $currentTick,
                initialStrength: new MythStrength($element['strength']),
                sourceType: 'narrative_canonization',
                sourceId: $chapter->getId(),
            );

            $this->mythRepository->save($myth);
            $myths[] = $myth;
        }

        return $myths;
    }

    /**
     * Simple extraction — real implementation would use LLM.
     *
     * @return array<int, array{name: string, description: string, strength: float}>
     */
    private function extractNarrativeElements(string $text): array
    {
        $elements = [];
        $wordCount = str_word_count($text);

        // Generate a myth if chapter has substantial content
        if ($wordCount > 20) {
            // Extract first sentence as myth name
            $firstSentence = strtok($text, ".!?\n");
            $name = mb_substr(trim($firstSentence ?: 'Unknown Event'), 0, 100);

            $elements[] = [
                'name' => $name,
                'description' => mb_substr($text, 0, 500),
                'strength' => min(0.8, $wordCount / 500), // Longer = stronger myth
            ];
        }

        return $elements;
    }
}
