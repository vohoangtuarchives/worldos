<?php

namespace WorldOS\Domains\Evolution;

interface ScarRepository
{
    public function save(Scar $scar): void;
    
    /**
     * @return Scar[]
     */
    public function findByWorld(string $worldId): array;
}

