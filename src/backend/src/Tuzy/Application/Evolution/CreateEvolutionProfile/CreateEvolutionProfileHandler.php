<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\CreateEvolutionProfile;

use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

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
