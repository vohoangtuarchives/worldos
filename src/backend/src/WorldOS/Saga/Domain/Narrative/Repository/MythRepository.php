<?php

namespace WorldOS\Saga\Domain\Narrative\Repository;

use WorldOS\Saga\Domain\Narrative\Entity\Myth;

interface MythRepository
{
    public function save(Myth $myth): void;
    
    /**
     * @return Myth[]
     */
    public function findByWorld(string $worldId): array;
}
