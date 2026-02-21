<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\GetSaga;

use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Exception\SagaNotFoundException;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class GetSagaHandler
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
    ) {
    }

    public function handle(GetSagaQuery $query): Saga
    {
        $saga = $this->sagaRepository->findById($query->id);
        if ($saga === null) {
            throw SagaNotFoundException::withId($query->id);
        }
        return $saga;
    }
}
