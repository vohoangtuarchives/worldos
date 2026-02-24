<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\GetEvolutionProfile;

use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Exception\EvolutionProfileNotFoundException;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class GetEvolutionProfileHandler
{
    public function __construct(
        private readonly EvolutionProfileRepositoryInterface $repository,
    ) {
    }

    public function handle(GetEvolutionProfileQuery $query): EvolutionProfile
    {
        $profile = $this->repository->findById($query->id);
        if ($profile === null) {
            throw EvolutionProfileNotFoundException::withId($query->id);
        }
        return $profile;
    }
}
