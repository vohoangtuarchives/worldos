<?php

namespace Tuzy\Domain\Narrative\Repository;

use Tuzy\Domain\Narrative\Entity\Archive;

interface ArchiveRepository
{
    public function save(Archive $archive): void;
    public function findByWorld(string $worldId): ?Archive;
}
