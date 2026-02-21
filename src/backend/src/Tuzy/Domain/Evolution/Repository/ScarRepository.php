<?php

namespace Tuzy\Domain\Evolution\Repository;

use Tuzy\Domain\Evolution\Entity\Scar;

interface ScarRepository
{
    public function save(Scar $scar): void;
    
    /**
     * @return Scar[]
     */
    public function findByWorld(string $worldId): array;
}

