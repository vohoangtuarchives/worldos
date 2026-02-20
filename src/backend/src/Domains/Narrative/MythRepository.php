<?php

namespace WorldOS\Domains\Narrative;

interface MythRepository
{
    public function save(Myth $myth): void;
    
    /**
     * @return Myth[]
     */
    public function findByWorld(string $worldId): array;
}
