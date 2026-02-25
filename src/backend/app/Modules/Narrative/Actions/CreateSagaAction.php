<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Actions;

use App\Modules\Narrative\Contracts\SagaRepositoryInterface;
use App\Modules\Narrative\Dto\CreateSagaDTO;
use App\Modules\Narrative\Entities\SagaEntity;
use App\Modules\Narrative\Events\SagaStarted;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Narrative\ValueObjects\SagaId;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Create Saga Action — initializes a new experiment session.
 */
final class CreateSagaAction
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    public function handle(CreateSagaDTO $dto): SagaEntity
    {
        $saga = new SagaEntity(
            id: SagaId::generate(),
            name: $dto->name,
            presetKey: $dto->presetKey,
            createdAt: new DateTimeImmutable(),
        );

        foreach ($dto->universeIds as $idx => $uid) {
            $uIdObj = new UniverseId($uid);
            $universe = $this->universeRepository->findById($uIdObj);
            if ($universe) {
                // Sequence is 1-based index (idx + 1)
                $saga->addWorld($universe->getWorldId(), $uIdObj, $idx + 1);
            }
        }

        $this->sagaRepository->save($saga);

        $this->eventDispatcher->dispatch(new SagaStarted(
            sagaId: $saga->getId(),
            name: $saga->getName(),
            presetKey: $saga->getPresetKey(),
        ));

        return $saga;
    }
}
