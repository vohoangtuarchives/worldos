<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\UpdateWorld;

use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Exception\WorldNotFoundException;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

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
        $world = World::create(
            $command->name,
            $command->id,
            $command->status,
            $command->healthStatus,
            $command->currentTick,
            $command->originType,
            $command->preset
        );
        $this->worldRepository->save($world);
    }
}
