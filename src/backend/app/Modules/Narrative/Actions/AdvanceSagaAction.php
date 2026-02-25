<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Actions;

use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Narrative\Contracts\SagaRepositoryInterface;
use App\Modules\Narrative\Jobs\AdvanceSagaJob;
use App\Modules\Narrative\ValueObjects\SagaId;
use Illuminate\Contracts\Events\Dispatcher;
use LogicException;

/**
 * Advance Saga Action — advances all Universes in a Saga by N ticks.
 */
final class AdvanceSagaAction
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    /**
     * @return array<string, mixed> Basic ack response
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

        // Add target_tick to each universe and save
        $universeIds = $saga->getUniverseIds();
        foreach ($universeIds as $universeIdStr) {
            $uId = UniverseId::fromString($universeIdStr);
            $universe = $this->universeRepository->findById($uId);
            if ($universe !== null) {
                // Ensure currentTargetTick handles null initially
                $currentTarget = $universe->getTargetTick() ?? $universe->getCurrentTick();
                $universe->setTargetTick($currentTarget + $ticks);
                $this->universeRepository->save($universe);
            }
        }

        // Dispatch background job to orchestrate the start
        AdvanceSagaJob::dispatch($sagaId, $ticks);

        return [
            'status' => 'accepted',
            'message' => "Advance Saga dispatched for {$ticks} ticks.",
            'saga_id' => $sagaId,
        ];
    }
}
