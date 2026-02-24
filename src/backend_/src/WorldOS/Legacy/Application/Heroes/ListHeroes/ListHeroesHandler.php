<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\ListHeroes;

use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;

final class ListHeroesHandler
{
    public function __construct(
        private readonly HeroRepositoryInterface $HeroRepository,
    ) {
    }

    public function handle(ListHeroesQuery $query): ListHeroesResult
    {
        $entities = $this->HeroRepository->findAll();
        $items = [];
        foreach ($entities as $entity) {
            $items[] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'world_id' => $entity->getWorldId(),
            ];
        }
        return new ListHeroesResult($items);
    }
}
