<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\GetEvolutionProfile;

use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Exception\EvolutionProfileNotFoundException;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

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
