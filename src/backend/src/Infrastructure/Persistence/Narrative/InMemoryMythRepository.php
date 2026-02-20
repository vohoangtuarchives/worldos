<?php

namespace WorldOS\Infrastructure\Persistence\Narrative;

use WorldOS\Domains\Narrative\MythRepository;
use WorldOS\Domains\Narrative\Myth;

class InMemoryMythRepository implements MythRepository
{
    /** @var Myth[] */
    private array $myths = [];

    public function save(Myth $myth): void
    {
        $this->myths[$myth->getId()] = $myth;
    }

    public function findByWorld(string $worldId): array
    {
        $result = [];
        foreach ($this->myths as $myth) {
            $reflection = new \ReflectionClass(Myth::class);
            $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
            
            if ($worldIdProp->getValue($myth) === $worldId) {
                $result[] = $myth;
            }
        }
        return $result;
    }
}
