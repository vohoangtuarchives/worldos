<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Actions;

use App\WorldOS\Saga\Contracts\SagaRepositoryInterface;
use App\WorldOS\Saga\Dto\CreateSagaDTO;
use App\WorldOS\Saga\Entities\SagaEntity;
use App\WorldOS\Saga\Events\SagaStarted;
use App\WorldOS\Saga\ValueObjects\SagaId;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Create Saga Action — initializes a new experiment session.
 */
final class CreateSagaAction
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
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

        $this->sagaRepository->save($saga);

        $this->eventDispatcher->dispatch(new SagaStarted(
            sagaId: $saga->getId(),
            name: $saga->getName(),
            presetKey: $saga->getPresetKey(),
        ));

        return $saga;
    }
}
