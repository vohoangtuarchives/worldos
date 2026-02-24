<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Services;

use App\WorldOS\Governance\Contracts\UniverseEvaluatorInterface;
use App\WorldOS\Governance\ValueObjects\EvaluationResult;
use App\WorldOS\Governance\ValueObjects\UniverseMetrics;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;

/**
 * Decision Engine — applies governance evaluation results.
 *
 * From docs §13.2: DecisionEngine áp dụng;
 *   fork = clone Universe, archive = status archived, continue = optional applyPressure.
 *
 * Orchestrates: MetricsExtractor → Evaluator → Action.
 */
final class DecisionEngine
{
    public function __construct(
        private readonly MetricsExtractor $extractor,
        private readonly UniverseEvaluatorInterface $evaluator,
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly UniverseSnapshotRepositoryInterface $snapshotRepository,
    ) {
    }

    /**
     * Evaluate a Universe and apply the governance decision.
     *
     * @return array{metrics: UniverseMetrics, result: EvaluationResult, action_taken: string}
     */
    public function evaluateAndAct(UniverseId $universeId): array
    {
        $universe = $this->universeRepository->findById($universeId);

        if (!$universe) {
            return [
                'metrics' => null,
                'result' => null,
                'action_taken' => 'universe_not_found',
            ];
        }

        // Extract metrics from snapshot history
        $snapshots = $this->snapshotRepository->getHistory($universeId, limit: 20);
        $metrics = $this->extractor->extract($universe, $snapshots);

        // Evaluate
        $result = $this->evaluator->evaluate($metrics);

        // Apply decision
        $action = $this->applyDecision($universe, $result);

        return [
            'metrics' => $metrics,
            'result' => $result,
            'action_taken' => $action,
        ];
    }

    /**
     * Evaluate only — no side effects.
     */
    public function evaluateOnly(UniverseId $universeId): array
    {
        $universe = $this->universeRepository->findById($universeId);

        if (!$universe) {
            return ['metrics' => null, 'result' => null];
        }

        $snapshots = $this->snapshotRepository->getHistory($universeId, limit: 20);
        $metrics = $this->extractor->extract($universe, $snapshots);
        $result = $this->evaluator->evaluate($metrics);

        return ['metrics' => $metrics, 'result' => $result];
    }

    private function applyDecision(UniverseEntity $universe, EvaluationResult $result): string
    {
        return match ($result->recommendation) {
            EvaluationResult::ARCHIVE => $this->archiveUniverse($universe),
            EvaluationResult::FORK => $this->forkUniverse($universe),
            EvaluationResult::CONTINUE => 'continued',
            default => 'no_action',
        };
    }

    private function archiveUniverse(UniverseEntity $universe): string
    {
        $universe->archive();
        $this->universeRepository->save($universe);

        return 'archived';
    }

    private function forkUniverse(UniverseEntity $universe): string
    {
        // Fork creates a snapshot marker — actual forking
        // is handled by SpawnUniverseAction with parent_universe_id
        // Here we just mark the recommendation for the Saga orchestrator
        return 'fork_recommended';
    }
}
