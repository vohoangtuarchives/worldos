<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\DeleteWorld;

use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

final class DeleteWorldHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(DeleteWorldCommand $command): void
    {
        $this->worldRepository->delete($command->id);
    }
}
