<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\ListWorldHeroes;

use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class ListWorldHeroesHandler
{
    public function __construct(
        private readonly WorldHeroRepositoryInterface $worldHeroRepository,
    ) {
    }

    public function handle(ListWorldHeroesQuery $query): ListWorldHeroesResult
    {
        $entities = $this->worldHeroRepository->findAll();
        $items = [];
        foreach ($entities as $entity) {
            $items[] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'world_id' => $entity->getWorldId(),
            ];
        }
        return new ListWorldHeroesResult($items);
    }
}
