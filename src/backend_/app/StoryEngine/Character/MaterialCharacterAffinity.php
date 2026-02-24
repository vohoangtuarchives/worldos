<?php

namespace App\StoryEngine\Character;

use Illuminate\Support\Facades\File;

class MaterialCharacterAffinity
{
    private array $affinities;

    public function __construct()
    {
        $path = app_path('StoryEngine/Character/Data/character_archetypes.json');
        $json = File::get($path);
        $this->affinities = json_decode($json, true);
    }

    /**
     * Get character archetypes for a material code.
     */
    public function getArchetypes(string $materialCode): array
    {
        return $this->affinities[$materialCode]['character_archetypes'] ?? [];
    }

    /**
     * Get traits for a material code.
     */
    public function getTraits(string $materialCode): array
    {
        return $this->affinities[$materialCode]['traits'] ?? [];
    }

    /**
     * Get narrative roles for a material code.
     */
    public function getNarrativeRoles(string $materialCode): array
    {
        return $this->affinities[$materialCode]['narrative_roles'] ?? [];
    }

    /**
     * Get full affinity data for a material.
     */
    public function getAffinity(string $materialCode): ?array
    {
        return $this->affinities[$materialCode] ?? null;
    }

    /**
     * Get a random character archetype for a material.
     */
    public function getRandomArchetype(string $materialCode): ?string
    {
        $archetypes = $this->getArchetypes($materialCode);
        
        if (empty($archetypes)) {
            return null;
        }

        return $archetypes[array_rand($archetypes)];
    }

    /**
     * Get random traits (subset) for a material.
     */
    public function getRandomTraits(string $materialCode, int $count = 3): array
    {
        $traits = $this->getTraits($materialCode);
        
        if (empty($traits)) {
            return [];
        }

        shuffle($traits);
        return array_slice($traits, 0, min($count, count($traits)));
    }
}
