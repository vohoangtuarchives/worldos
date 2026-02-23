<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\GetSaga;

use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Exception\SagaNotFoundException;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

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
