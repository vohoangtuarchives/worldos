<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\GetUniverseStyle;

use Tuzy\Domain\Cosmology\Entity\UniverseStyle;
use Tuzy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

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
