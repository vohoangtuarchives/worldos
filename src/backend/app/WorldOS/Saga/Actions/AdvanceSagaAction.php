<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Actions;

use App\WorldOS\Cosmology\ValueObjects\EvolutionResult;
use App\WorldOS\Runtime\Actions\AdvanceUniverseAction;
use App\WorldOS\Saga\Contracts\SagaRepositoryInterface;
use App\WorldOS\Saga\Events\SagaCompleted;
use App\WorldOS\Saga\ValueObjects\SagaId;
use Illuminate\Contracts\Events\Dispatcher;
use LogicException;

/**
 * Advance Saga Action — advances all Universes in a Saga by N ticks.
 */
final class AdvanceSagaAction
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
        private readonly AdvanceUniverseAction $advanceUniverse,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    /**
     * @return array<string, EvolutionResult[]> Results keyed by universe_id
     */
    public function handle(string $sagaId, int $ticks = 1): array
    {
        $id = SagaId::fromString($sagaId);
        $saga = $this->sagaRepository->findById($id);

        if ($saga === null) {
            throw new LogicException("Saga [{$sagaId}] not found");
        }

        if (!$saga->canAdvance()) {
            throw new LogicException(
                "Saga [{$sagaId}] cannot advance: status [{$saga->getStatus()->value}]"
            );
        }

        $allResults = [];
        $totalTicks = 0;

        foreach ($saga->getUniverseIds() as $universeId) {
            $results = $this->advanceUniverse->handle($universeId, $ticks);
            $allResults[$universeId] = $results;
            $totalTicks += count($results);
        }

        // Check if all universes have collapsed
        $allCollapsed = true;
        foreach ($allResults as $results) {
            $lastResult = end($results);
            if ($lastResult && !$lastResult->collapseDetected) {
                $allCollapsed = false;
                break;
            }
        }

        if ($allCollapsed && !empty($allResults)) {
            $saga->complete();
            $this->sagaRepository->save($saga);

            $this->eventDispatcher->dispatch(new SagaCompleted(
                sagaId: $id,
                totalTicks: $totalTicks,
            ));
        }

        return $allResults;
    }
}
