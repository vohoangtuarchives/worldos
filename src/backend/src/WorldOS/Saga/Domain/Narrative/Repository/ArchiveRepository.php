<?php

namespace WorldOS\Saga\Domain\Narrative\Repository;

use WorldOS\Saga\Domain\Narrative\Entity\Archive;

interface ArchiveRepository
{
    public function save(Archive $archive): void;
    public function findByWorld(string $worldId): ?Archive;
}
