<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\CreateSaga;

use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class CreateSagaHandler
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
    ) {
    }

    public function handle(CreateSagaCommand $command): CreateSagaResult
    {
        $saga = Saga::create($command->name);
        $this->sagaRepository->save($saga);
        return new CreateSagaResult($saga->getId(), $saga->getName());
    }
}
