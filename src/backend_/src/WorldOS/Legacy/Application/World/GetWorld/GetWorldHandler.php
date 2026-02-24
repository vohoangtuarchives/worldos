<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\GetWorld;

use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Exception\WorldNotFoundException;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

final class GetWorldHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(GetWorldQuery $query): World
    {
        $world = $this->worldRepository->findById($query->id);
        if ($world === null) {
            throw WorldNotFoundException::withId($query->id);
        }
        return $world;
    }
}
