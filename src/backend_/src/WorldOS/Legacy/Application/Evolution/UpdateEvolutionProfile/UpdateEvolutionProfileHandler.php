<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\UpdateEvolutionProfile;

use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Exception\EvolutionProfileNotFoundException;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

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
