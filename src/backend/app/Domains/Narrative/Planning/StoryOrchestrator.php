<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5.5: High-level orchestration — generateArc → planChapters → produceChapter → evaluate → refineIfNeeded.
 */
class StoryOrchestrator
{
    public function __construct(
        private readonly NarrativePlanningEngine $planningEngine,
        private readonly ChapterProducer $chapterProducer,
        private readonly QualityControlEngine $qualityControl
    ) {
    }

    /**
     * Generate arc (plan), plan chapters, produce first chapter, evaluate, optionally refine.
     * Does not dispatch jobs; call from a job or controller that can run async.
     *
     * @param array<string, mixed> $worldState
     * @param array<int, array> $pressurePoints
     * @param string|null $arcId Optional existing arc id to attach blueprints to
     * @return array{arcs: array, blueprints: array, draft: string|null, score: float|null, refined: bool}
     */
    public function generateArcAndFirstChapter(array $worldState, array $pressurePoints, ?string $arcId = null): array
    {
        $arcs = $this->planningEngine->planArcs($worldState, $pressurePoints);
        $firstArc = $arcs[0] ?? null;
        if ($firstArc === null) {
            return ['arcs' => [], 'blueprints' => [], 'draft' => null, 'score' => null, 'refined' => false];
        }

        $blueprints = $this->planningEngine->planChapters($firstArc);
        $firstBlueprint = $blueprints[0] ?? null;
        if ($firstBlueprint === null) {
            return ['arcs' => $arcs, 'blueprints' => $blueprints, 'draft' => null, 'score' => null, 'refined' => false];
        }

        if ($arcId !== null && Schema::hasTable('narrative_chapter_blueprints')) {
            foreach ($blueprints as $bp) {
                DB::table('narrative_chapter_blueprints')->insert([
                    'arc_id' => $arcId,
                    'chapter_index' => $bp['chapter_index'],
                    'emotional_objective' => $bp['emotional_objective'],
                    'conflict_delta' => json_encode($bp['conflict_delta'] ?? []),
                    'motif_targets' => json_encode($bp['motif_targets'] ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $draft = $this->chapterProducer->produce($firstBlueprint, $worldState);
        $eval = $this->qualityControl->evaluateWithRubric($draft, $firstBlueprint);
        $refined = null;
        if ($eval['score'] < QualityControlEngine::MIN_SCORE_PASS) {
            $refined = $this->qualityControl->refineIfNeeded($draft, $firstBlueprint, $this->chapterProducer, $worldState);
        }
        $finalDraft = $refined ?? $draft;
        $finalScore = $refined !== null
            ? $this->qualityControl->evaluateWithRubric($refined, $firstBlueprint)['score']
            : $eval['score'];

        return [
            'arcs' => $arcs,
            'blueprints' => $blueprints,
            'draft' => $finalDraft,
            'score' => $finalScore,
            'refined' => $refined !== null,
        ];
    }
}
