<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\Repository;

use WorldOS\Saga\Domain\Hero\Entity\Hero;

interface HeroRepositoryInterface
{
    public function findById(string $id): ?Hero;
    public function save(Hero $hero): void;
    public function delete(string $id): void;
    /** @return Hero[] */
    public function findByWorld(string $worldId): array;
    /** @return Hero[] */
    public function findAll(): array;
}
