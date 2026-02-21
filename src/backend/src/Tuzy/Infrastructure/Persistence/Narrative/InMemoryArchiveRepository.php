<?php

namespace Tuzy\Infrastructure\Persistence\Narrative;

use Tuzy\Domain\Narrative\Repository\ArchiveRepository;
use Tuzy\Domain\Narrative\Entity\Archive;

class InMemoryArchiveRepository implements ArchiveRepository
{
    private array $archives = [];

    public function save(Archive $archive): void
    {
        $reflection = new \ReflectionClass(Archive::class);
        $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
        $worldId = $worldIdProp->getValue($archive);
        
        $this->archives[$worldId] = $archive;
    }

    public function findByWorld(string $worldId): ?Archive
    {
        return $this->archives[$worldId] ?? null;
    }
}
