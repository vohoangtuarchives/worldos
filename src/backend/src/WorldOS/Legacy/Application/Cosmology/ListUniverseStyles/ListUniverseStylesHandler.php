<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\ListUniverseStyles;

use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class ListUniverseStylesHandler
{
    public function __construct(
        private readonly UniverseStyleRepositoryInterface $universeStyleRepository,
    ) {
    }

    public function handle(ListUniverseStylesQuery $query): ListUniverseStylesResult
    {
        $entities = $this->universeStyleRepository->findAll();
        $items = [];
        foreach ($entities as $entity) {
            $items[] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'world_id' => $entity->getWorldId(),
            ];
        }
        return new ListUniverseStylesResult($items);
    }
}
