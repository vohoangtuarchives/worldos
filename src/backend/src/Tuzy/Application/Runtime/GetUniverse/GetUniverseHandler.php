<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\GetUniverse;

use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

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
