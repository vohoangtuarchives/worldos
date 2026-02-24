<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\Saga\Entity;

use WorldOS\Chronicle\Domain\Saga\ValueObject\SagaId;

/**
 * The V5 Saga Entity (Chronicle Context).
 * This entity is responsible for grouping Universes and recording History.
 * It observes the Simulation Engine but does not execute Math loops itself.
 */
final class Saga
{
    /** @var string[] */
    private array $universeIds = [];

    private function __construct(
        private SagaId $id,
        private string $name,
        private string $worldBlueprintId // A Saga is tied to a specific ruleset
    ) {
    }

    public static function create(string $name, string $worldBlueprintId, ?SagaId $id = null): self
    {
        return new self(
            $id ?? SagaId::generate(),
            $name,
            $worldBlueprintId
        );
    }

    /**
     * When the Engine spawns a new Universe, Chronicle attaches it here.
     */
    public function attachUniverse(string $universeId): void
    {
        if (!in_array($universeId, $this->universeIds, true)) {
            $this->universeIds[] = $universeId;
        }
    }

    public function recordEvent(string $universeId, int $atTick, string $eventData): void
    {
        // Generate a SagaEvent and persist it...
    }

    public function getId(): SagaId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWorldBlueprintId(): string
    {
        return $this->worldBlueprintId;
    }

    public function getAttachedUniverses(): array
    {
        return $this->universeIds;
    }
}
