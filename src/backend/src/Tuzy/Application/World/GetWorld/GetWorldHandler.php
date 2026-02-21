<?php

declare(strict_types=1);

namespace Tuzy\Application\World\GetWorld;

use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

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
