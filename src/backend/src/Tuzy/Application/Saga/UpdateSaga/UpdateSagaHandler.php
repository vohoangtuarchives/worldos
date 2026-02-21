<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\UpdateSaga;

use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Exception\SagaNotFoundException;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

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
