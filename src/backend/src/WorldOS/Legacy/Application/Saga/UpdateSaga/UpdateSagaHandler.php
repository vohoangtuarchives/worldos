<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\UpdateSaga;

use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Exception\SagaNotFoundException;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class UpdateSagaHandler
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
    ) {
    }

    public function handle(UpdateSagaCommand $command): void
    {
        $existing = $this->sagaRepository->findById($command->id);
        if ($existing === null) {
            throw SagaNotFoundException::withId($command->id);
        }
        $saga = Saga::create($command->name, $command->id);
        $this->sagaRepository->save($saga);
    }
}
