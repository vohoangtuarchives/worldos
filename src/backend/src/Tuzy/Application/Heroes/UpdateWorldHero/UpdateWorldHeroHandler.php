<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\UpdateWorldHero;

use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Exception\WorldHeroNotFoundException;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class UpdateWorldHeroHandler
{
    public function __construct(
        private readonly WorldHeroRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateWorldHeroCommand $command): void
    {
        $existing = $this->repository->findById($command->id);
        if ($existing === null) {
            throw WorldHeroNotFoundException::withId($command->id);
        }
        $hero = WorldHero::create($command->name, $existing->getWorldId(), $command->id);
        $this->repository->save($hero);
    }
}
