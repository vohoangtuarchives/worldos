<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Entities;

use Ramsey\Uuid\Uuid;

/**
 * Story Bible Entity — long-term narrative memory.
 *
 * From docs §11.2: Characters, Locations, Lore, Relations.
 * Universe = Truth (State Vector); StoryBible = Meaning.
 *
 * Pure PHP — NO Eloquent.
 */
final class StoryBibleEntity
{
    /**
     * @param string   $id
     * @param string   $seriesId
     * @param array<int, array{name: string, role: string, traits: string[], status: string}> $characters
     * @param array<int, array{name: string, type: string, description: string}> $locations
     * @param array<int, array{key: string, description: string, source_chapter: int}> $lore
     * @param array<int, array{from: string, to: string, type: string}> $relations
     */
    public function __construct(
        private readonly string $id,
        private readonly string $seriesId,
        private array $characters,
        private array $locations,
        private array $lore,
        private array $relations,
    ) {
    }

    public static function empty(string $seriesId): self
    {
        return new self(
            id: Uuid::uuid4()->toString(),
            seriesId: $seriesId,
            characters: [],
            locations: [],
            lore: [],
            relations: [],
        );
    }

    // ── Getters ──

    public function getId(): string
    {
        return $this->id;
    }

    public function getSeriesId(): string
    {
        return $this->seriesId;
    }

    public function getCharacters(): array
    {
        return $this->characters;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }

    public function getLore(): array
    {
        return $this->lore;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    // ── Mutation Methods ──

    public function addCharacter(string $name, string $role, array $traits = [], string $status = 'alive'): void
    {
        $this->characters[] = [
            'name' => $name,
            'role' => $role,
            'traits' => $traits,
            'status' => $status,
        ];
    }

    public function addLocation(string $name, string $type, string $description): void
    {
        $this->locations[] = [
            'name' => $name,
            'type' => $type,
            'description' => $description,
        ];
    }

    public function addLore(string $key, string $description, int $sourceChapter): void
    {
        $this->lore[] = [
            'key' => $key,
            'description' => $description,
            'source_chapter' => $sourceChapter,
        ];
    }

    public function addRelation(string $from, string $to, string $type): void
    {
        $this->relations[] = [
            'from' => $from,
            'to' => $to,
            'type' => $type,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'characters' => $this->characters,
            'locations' => $this->locations,
            'lore' => $this->lore,
            'relations' => $this->relations,
        ];
    }
}
