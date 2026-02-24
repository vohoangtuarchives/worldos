<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\ListWorlds;

use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

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
            $worlds[] = [
                'id' => $world->getId(), 
                'name' => $world->getName(),
                'status' => $world->getStatus(),
                'health_status' => $world->getHealthStatus(),
                'current_tick' => $world->getCurrentTick(),
                'origin_type' => $world->getOriginType(),
                'preset' => $world->getPreset(),
                'config' => $world->getConfig(),
                'gene_vector' => $world->getGeneVector(),
            ];
        }
        return new ListWorldsResult($worlds);
    }
}
