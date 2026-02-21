<?php

namespace WorldOS\Domains\Narrative;

interface ArchiveRepository
{
    public function save(Archive $archive): void;
    public function findByWorld(string $worldId): ?Archive;
}
