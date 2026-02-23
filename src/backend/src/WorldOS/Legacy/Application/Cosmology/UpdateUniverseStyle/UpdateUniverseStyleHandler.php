<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\UpdateUniverseStyle;

use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class UpdateUniverseStyleHandler
{
    public function __construct(
        private readonly UniverseStyleRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateUniverseStyleCommand $command): void
    {
        $existing = $this->repository->findById($command->id);
        if ($existing === null) {
            throw UniverseStyleNotFoundException::withId($command->id);
        }
        $style = UniverseStyle::create($command->name, $existing->getWorldId(), $command->id);
        $this->repository->save($style);
    }
}
