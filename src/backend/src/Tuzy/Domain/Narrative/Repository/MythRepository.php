<?php

namespace Tuzy\Domain\Narrative\Repository;

use Tuzy\Domain\Narrative\Entity\Myth;

interface MythRepository
{
    public function save(Myth $myth): void;
    
    /**
     * @return Myth[]
     */
    public function findByWorld(string $worldId): array;
}
