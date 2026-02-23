<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\CreateEvolutionProfile;

use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class CreateEvolutionProfileHandler
{
    public function __construct(
        private readonly EvolutionProfileRepositoryInterface $repository,
    ) {
    }

    public function handle(CreateEvolutionProfileCommand $command): CreateEvolutionProfileResult
    {
        $profile = EvolutionProfile::create($command->name);
        $this->repository->save($profile);
        return new CreateEvolutionProfileResult($profile->getId(), $profile->getName());
    }
}
