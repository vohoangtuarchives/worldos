<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\UpdateUniverseStyle;

use Tuzy\Domain\Cosmology\Entity\UniverseStyle;
use Tuzy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

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
