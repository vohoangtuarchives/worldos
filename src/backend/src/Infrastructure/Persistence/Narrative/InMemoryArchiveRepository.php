<?php

namespace WorldOS\Infrastructure\Persistence\Narrative;

use WorldOS\Domains\Narrative\ArchiveRepository;
use WorldOS\Domains\Narrative\Archive;

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
