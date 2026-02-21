<?php

declare(strict_types=1);

namespace App\Listeners;

use Tuzy\Domain\Runtime\Event\UniverseCollapsed;
use Tuzy\Domain\Runtime\Event\UniverseForked;
use Tuzy\Domain\Runtime\Event\UniverseTicked;
use Tuzy\Domain\Saga\ValueObject\SagaEvaluationReport;
use Tuzy\Application\Saga\Services\BlueprintMutationPlanner;
use Tuzy\Application\Saga\Services\CivilizationScorer;
use Tuzy\Application\Saga\Services\ParetoFrontManager;
use Tuzy\Application\Saga\Services\SagaMetaEvaluator;
use Tuzy\Domain\Saga\SagaObserver;
use Tuzy\Domain\Saga\SagaWorld;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2: Subscribe to Universe runtime events. Phase 4: On UniverseCollapsed, score + blueprint plan for next World.
 * Phase 4.4: Optional ParetoFrontManager to record saga_generations. Phase 5: Optional SagaMetaEvaluator (Layer 1+2).
 */
class UniverseRuntimeEventSubscriber
{
    public function __construct(
        private readonly SagaObserver $observer,
        private readonly ?CivilizationScorer $civilizationScorer = null,
        private readonly ?BlueprintMutationPlanner $blueprintPlanner = null,
        private readonly ?ParetoFrontManager $paretoFrontManager = null,
        private readonly ?SagaMetaEvaluator $sagaMetaEvaluator = null
    ) {
    }

    public function subscribe(object $events): void
    {
        $events->listen(UniverseTicked::class, [$this, 'onUniverseTicked']);
        $events->listen(UniverseCollapsed::class, [$this, 'onUniverseCollapsed']);
        $events->listen(UniverseForked::class, [$this, 'onUniverseForked']);
    }

    public function onUniverseTicked(UniverseTicked $event): void
    {
        Log::debug('UniverseTicked', [
            'universe_id' => $event->universeId,
            'world_id' => $event->worldId,
            'age' => $event->age,
        ]);
    }

    public function onUniverseCollapsed(UniverseCollapsed $event): void
    {
        $sagaWorld = SagaWorld::where('universe_id', $event->universeId)->first();
        if ($sagaWorld === null && $event->worldId !== null) {
            $sagaWorld = SagaWorld::where('world_id', $event->worldId)->orderByDesc('sequence')->first();
        }
        if ($sagaWorld !== null) {
            $context = [
                'cause' => $event->cause,
                'final_state' => $event->finalState,
            ];
            $report = $this->resolveReport($event->cause, $event->finalState);
            if ($report !== null && $this->blueprintPlanner !== null) {
                $context['blueprint_plan'] = $this->blueprintPlanner->planFromReport(
                    $report,
                    $sagaWorld->saga_id,
                    $sagaWorld->sequence
                );
                $entropy = (float) ($event->finalState['entropy'] ?? 0.5);
                $context['objective_vector'] = [
                    'stability' => $report->stabilityScore,
                    'resilience' => $report->resilienceIndex,
                    'entropy_control' => 1.0 - $entropy,
                ];
                if ($this->paretoFrontManager !== null) {
                    $this->paretoFrontManager->record(
                        $sagaWorld->saga_id,
                        (int) $sagaWorld->world_id,
                        $sagaWorld->sequence,
                        $context['objective_vector'],
                        null
                    );
                }
            }
            $sagaWorld->markAsCollapsed($context);
            $saga = $sagaWorld->saga;
            if ($saga !== null) {
                $this->observer->observe($saga, $sagaWorld);
            }
        }
    }

    private function resolveReport(string $cause, array $finalState): ?SagaEvaluationReport
    {
        if ($this->sagaMetaEvaluator !== null) {
            return $this->sagaMetaEvaluator->evaluate($cause, $finalState);
        }
        if ($this->civilizationScorer !== null) {
            return $this->civilizationScorer->scoreFromCollapse($cause, $finalState);
        }
        return null;
    }

    public function onUniverseForked(UniverseForked $event): void
    {
        Log::debug('UniverseForked', [
            'source_universe_id' => $event->sourceUniverseId,
            'new_universe_id' => $event->newUniverseId,
            'world_id' => $event->worldId,
        ]);
    }
}
