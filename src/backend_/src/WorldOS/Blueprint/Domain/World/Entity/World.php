<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\Entity;

use WorldOS\Blueprint\Domain\World\ValueObject\WorldId;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldStatus;
use WorldOS\Blueprint\Domain\World\ValueObject\PhysicsCore;
use WorldOS\Blueprint\Domain\World\ValueObject\NarrativeTopology;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldSignature;

/**
 * The V5 World Entity.
 * Represents a static Blueprint / Genome from which dynamic Universes are spawned.
 * It is time-invariant and holds no current simulation tick or operational state.
 * Forms the root of the World lineage DAG.
 */
final class World
{
    private function __construct(
        private WorldId $id,
        private string $name,
        private WorldStatus $status,
        private PhysicsCore $physicsCore,
        private NarrativeTopology $narrativeTopology,
        private WorldSignature $signature,
        private string $multiverseId,
        private ?string $parentWorldId = null,
        private int $eraIndex = 0,
        private ?\DateTimeImmutable $createdAt = null
    ) {
    }

    /**
     * Create a new World blueprint.
     */
    public static function create(
        string $name,
        PhysicsCore $physicsCore,
        NarrativeTopology $narrativeTopology,
        string $multiverseId,
        ?string $parentWorldId = null,
        int $eraIndex = 0,
        ?WorldId $id = null,
        ?WorldStatus $status = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        $signature = WorldSignature::generate($physicsCore, $narrativeTopology);
        
        return new self(
            $id ?? WorldId::generate(),
            $name,
            $status ?? WorldStatus::DRAFT,
            $physicsCore,
            $narrativeTopology,
            $signature,
            $multiverseId,
            $parentWorldId,
            $eraIndex,
            $createdAt ?? new \DateTimeImmutable()
        );
    }

    /**
     * Seal the World blueprint so that it cannot be modified, 
     * ensuring consistency for all Universes spawned from it.
     */
    public function seal(): void
    {
        $this->status = WorldStatus::SEALED;
    }

    public function archive(): void
    {
        $this->status = WorldStatus::ARCHIVED;
    }

    // --- Getters ---

    public function getId(): WorldId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): WorldStatus
    {
        return $this->status;
    }

    public function getPhysicsCore(): PhysicsCore
    {
        return $this->physicsCore;
    }

    public function getNarrativeTopology(): NarrativeTopology
    {
        return $this->narrativeTopology;
    }

    public function getSignature(): WorldSignature
    {
        return $this->signature;
    }
    
    public function getMultiverseId(): string
    {
        return $this->multiverseId;
    }

    public function getParentWorldId(): ?string
    {
        return $this->parentWorldId;
    }

    public function getEraIndex(): int
    {
        return $this->eraIndex;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
