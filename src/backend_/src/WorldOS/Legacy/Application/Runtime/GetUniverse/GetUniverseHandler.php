<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\GetUniverse;

use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Exception\UniverseNotFoundException;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class GetUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    public function handle(GetUniverseQuery $query): Universe
    {
        $universe = $this->universeRepository->findById($query->id);
        if ($universe === null) {
            throw UniverseNotFoundException::withId($query->id);
        }
        return $universe;
    }
}
