<?php

declare(strict_types=1);

namespace Tuzy\Application\World\UpdateWorld;

use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class UpdateWorldHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(UpdateWorldCommand $command): void
    {
        $existing = $this->worldRepository->findById($command->id);
        if ($existing === null) {
            throw WorldNotFoundException::withId($command->id);
        }
        $world = World::create($command->name, $command->id);
        $this->worldRepository->save($world);
    }
}
