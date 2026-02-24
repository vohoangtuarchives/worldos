<?php

namespace App\StoryEngine\Character;

class Character
{
    public string $id;
    public string $name;
    public string $archetype;
    public array $traits;
    public array $narrativeRoles;
    public string $sourceMaterial;
    public ?string $worldId;
    public array $metadata;

    public function __construct(
        string $name,
        string $archetype,
        array $traits,
        array $narrativeRoles,
        string $sourceMaterial,
        ?string $worldId = null,
        array $metadata = []
    ) {
        $this->id = uniqid('char_');
        $this->name = $name;
        $this->archetype = $archetype;
        $this->traits = $traits;
        $this->narrativeRoles = $narrativeRoles;
        $this->sourceMaterial = $sourceMaterial;
        $this->worldId = $worldId;
        $this->metadata = $metadata;
    }

    /**
     * Get character description.
     */
    public function getDescription(): string
    {
        $traitList = implode(', ', $this->traits);
        return "{$this->name} is a {$this->archetype} characterized by {$traitList}.";
    }

    /**
     * Check if character has a specific trait.
     */
    public function hasTrait(string $trait): bool
    {
        return in_array($trait, $this->traits);
    }

    /**
     * Check if character can fulfill a narrative role.
     */
    public function canFulfillRole(string $role): bool
    {
        return in_array($role, $this->narrativeRoles);
    }

    /**
     * Convert to array for storage/serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'archetype' => $this->archetype,
            'traits' => $this->traits,
            'narrative_roles' => $this->narrativeRoles,
            'source_material' => $this->sourceMaterial,
            'world_id' => $this->worldId,
            'metadata' => $this->metadata,
        ];
    }
}
