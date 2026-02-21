<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\ListEvolutionProfiles;

use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

final class ListEvolutionProfilesHandler
{
    public function __construct(
        private readonly EvolutionProfileRepositoryInterface $evolutionProfileRepository,
    ) {
    }

    public function handle(ListEvolutionProfilesQuery $query): ListEvolutionProfilesResult
    {
        $entities = $this->evolutionProfileRepository->findAll();
        $items = [];
        foreach ($entities as $entity) {
            $items[] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
            ];
        }
        return new ListEvolutionProfilesResult($items);
    }
}
