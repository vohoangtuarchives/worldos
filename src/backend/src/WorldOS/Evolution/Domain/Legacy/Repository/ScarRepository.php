<?php

namespace WorldOS\Evolution\Domain\Legacy\Repository;

use WorldOS\Evolution\Domain\Legacy\Entity\Scar;

interface ScarRepository
{
    public function save(Scar $scar): void;
    
    /**
     * @return Scar[]
     */
    public function findByWorld(string $worldId): array;
}

