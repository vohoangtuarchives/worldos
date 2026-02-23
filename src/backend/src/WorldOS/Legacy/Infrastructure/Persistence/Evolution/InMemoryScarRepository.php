<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Evolution;

use WorldOS\Evolution\Domain\Legacy\Repository\ScarRepository;
use WorldOS\Evolution\Domain\Legacy\Entity\Scar;

class InMemoryScarRepository implements ScarRepository
{
    /** @var Scar[] */
    private array $scars = [];

    public function save(Scar $scar): void
    {
        $this->scars[$scar->getId()] = $scar;
    }

    public function findByWorld(string $worldId): array
    {
        $result = [];
        foreach ($this->scars as $scar) {
            if ($scar->getWorldId() === $worldId && $scar->getMagnitude() > 0) {
                $result[] = $scar;
            }
        }
        return $result;
    }
}
