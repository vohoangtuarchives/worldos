<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\GetUniverseStyle;

use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class GetUniverseStyleHandler
{
    public function __construct(
        private readonly UniverseStyleRepositoryInterface $repository,
    ) {
    }

    public function handle(GetUniverseStyleQuery $query): UniverseStyle
    {
        $style = $this->repository->findById($query->id);
        if ($style === null) {
            throw UniverseStyleNotFoundException::withId($query->id);
        }
        return $style;
    }
}
