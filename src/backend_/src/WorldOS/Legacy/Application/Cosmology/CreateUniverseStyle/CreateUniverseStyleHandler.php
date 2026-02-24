<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle;

use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class CreateUniverseStyleHandler
{
    public function __construct(
        private readonly UniverseStyleRepositoryInterface $universeStyleRepository,
    ) {
    }

    public function handle(CreateUniverseStyleCommand $command): CreateUniverseStyleResult
    {
        $universeStyle = UniverseStyle::create($command->name, $command->worldId);
        $this->universeStyleRepository->save($universeStyle);
        return new CreateUniverseStyleResult(
            $universeStyle->getId(),
            $universeStyle->getName(),
            $universeStyle->getWorldId(),
        );
    }
}
