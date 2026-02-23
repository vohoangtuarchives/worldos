<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\ListSagas;

use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class ListSagasHandler
{
    public function __construct(
        private readonly SagaRepositoryInterface $sagaRepository,
    ) {
    }

    public function handle(ListSagasQuery $query): ListSagasResult
    {
        $entities = $this->sagaRepository->findAll();
        $sagas = [];
        foreach ($entities as $s) {
            $sagas[] = ['id' => $s->getId(), 'name' => $s->getName()];
        }
        return new ListSagasResult($sagas);
    }
}
