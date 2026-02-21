<?php

declare(strict_types=1);

namespace Tuzy\Application\World\ListWorlds;

use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class ListWorldsHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(ListWorldsQuery $query): ListWorldsResult
    {
        $entities = $this->worldRepository->findAll();
        $worlds = [];
        foreach ($entities as $world) {
            $worlds[] = ['id' => $world->getId(), 'name' => $world->getName()];
        }
        return new ListWorldsResult($worlds);
    }
}
