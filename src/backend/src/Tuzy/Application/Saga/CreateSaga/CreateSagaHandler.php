<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\CreateSaga;

use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

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
