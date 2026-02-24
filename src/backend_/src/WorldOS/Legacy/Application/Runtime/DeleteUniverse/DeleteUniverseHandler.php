<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\DeleteUniverse;

use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class DeleteUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    public function handle(DeleteUniverseCommand $command): void
    {
        $this->universeRepository->delete($command->id);
    }
}
