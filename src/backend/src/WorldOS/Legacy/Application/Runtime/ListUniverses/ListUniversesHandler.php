<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\ListUniverses;

use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class ListUniversesHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    public function handle(ListUniversesQuery $query): ListUniversesResult
    {
        $entities = $this->universeRepository->findAll();
        $universes = [];
        foreach ($entities as $u) {
            $universes[] = ['id' => $u->getId(), 'name' => $u->getName()];
        }
        return new ListUniversesResult($universes);
    }
}
