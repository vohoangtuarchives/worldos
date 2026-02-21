<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\UpdateEvolutionProfile;

use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Exception\EvolutionProfileNotFoundException;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

final class UpdateEvolutionProfileHandler
{
    public function __construct(
        private readonly EvolutionProfileRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateEvolutionProfileCommand $command): void
    {
        $existing = $this->repository->findById($command->id);
        if ($existing === null) {
            throw EvolutionProfileNotFoundException::withId($command->id);
        }
        $profile = EvolutionProfile::create($command->name, $command->id);
        $this->repository->save($profile);
    }
}
