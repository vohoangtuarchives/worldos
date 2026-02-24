<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Universe\Entity;

use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseStatus;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * The V5 Universe Entity (Simulation Context).
 * Acts as the Run Instance of the Simulation Engine.
 * It is completely detached from Saga (Narrative) and expects only a Blueprint (WorldId) to run.
 */
final class Universe
{
    private function __construct(
        private UniverseId $id,
        private string $name,
        private string $worldBlueprintId, // References Blueprint Context (Weak Link)
        private string $worldSignatureHash, // Frozen copy of World's signature at Ignite time
        private string $multiverseId,      // Groups all universes in same DAG multiverse
        private UniverseStatus $status,
        private int $currentTick,
        private StateVector $stateVector,
        private \WorldOS\Society\Faction\ValueObject\IdeologyVector $ideology,
        private \WorldOS\Society\Culture\ValueObject\CulturalVector $culture,
        private \WorldOS\Core\ValueObject\LifecycleState $lifecycle,
        private float $influenceMass,
        private int $stabilityDuration,
        
        // V6 Lineage & Evolutionary Tracking
        private ?string $generationId = null,
        private ?string $parentUniverseId = null,
        private array $seedDna = [],
        private ?float $fitnessTotalScore = null,
        private ?int $lifespan = null
    ) {
    }

    /**
     * Ignite a new Universe from a SEALED World Blueprint.
     * Freezes the WorldSignature at the moment of ignition to guarantee
     * the Universe always runs under the exact physics it was born into.
     *
     * This is the canonical factory for production use.
     */
    public static function ignite(
        string $name,
        string $worldBlueprintId,
        string $worldSignatureHash,
        string $multiverseId,
        ?StateVector $initialStateVector = null,
        ?\WorldOS\Society\Faction\ValueObject\IdeologyVector $ideology = null,
        ?\WorldOS\Society\Culture\ValueObject\CulturalVector $culture = null,
        ?UniverseId $id = null,
        ?string $generationId = null,
        ?string $parentUniverseId = null,
        array $seedDna = []
    ): self {
        return new self(
            $id ?? UniverseId::generate(),
            $name,
            $worldBlueprintId,
            $worldSignatureHash,
            $multiverseId,
            UniverseStatus::PENDING,
            currentTick: 0,
            stateVector: $initialStateVector ?? StateVector::genesis(),
            ideology: $ideology ?? \WorldOS\Society\Faction\ValueObject\IdeologyVector::random(),
            culture: $culture ?? \WorldOS\Society\Culture\ValueObject\CulturalVector::default(),
            lifecycle: \WorldOS\Core\ValueObject\LifecycleState::Emerging,
            influenceMass: 1.0,
            stabilityDuration: 0,
            generationId: $generationId,
            parentUniverseId: $parentUniverseId,
            seedDna: $seedDna
        );
    }

    /**
     * Spawn a Universe without a World reference.
     * Kept for backward compatibility and testing purposes only.
     *
     * @deprecated Use Universe::ignite() in production flows.
     */
    public static function spawn(
        string $name,
        string $worldBlueprintId,
        string $multiverseId = 'default',
        array $initialStateVector = [],
        ?UniverseId $id = null
    ): self {
        return new self(
            $id ?? UniverseId::generate(),
            $name,
            $worldBlueprintId,
            worldSignatureHash: '',
            multiverseId: $multiverseId,
            status: UniverseStatus::PENDING,
            currentTick: 0,
            entropy: 0.0,
            stabilityIndex: 1.0,
            currentStateVector: $initialStateVector,
            generationId: null,
            parentUniverseId: null,
            seedDna: []
        );
    }

    /**
     * Restore a Universe from persistent storage.
     * This bypasses the normal initialization because we assume DB state is valid.
     */
    public static function restore(
        UniverseId $id,
        string $name,
        string $worldBlueprintId,
        string $worldSignatureHash,
        string $multiverseId,
        UniverseStatus $status,
        int $currentTick,
        StateVector $stateVector,
        \WorldOS\Society\Faction\ValueObject\IdeologyVector $ideology,
        \WorldOS\Society\Culture\ValueObject\CulturalVector $culture,
        \WorldOS\Core\ValueObject\LifecycleState $lifecycle,
        float $influenceMass,
        int $stabilityDuration,
        ?string $generationId,
        ?string $parentUniverseId,
        array $seedDna,
        ?float $fitnessTotalScore,
        ?int $lifespan
    ): self {
        return new self(
            $id,
            $name,
            $worldBlueprintId,
            $worldSignatureHash,
            $multiverseId,
            $status,
            $currentTick,
            $stateVector,
            $ideology,
            $culture,
            $lifecycle,
            $influenceMass,
            $stabilityDuration,
            $generationId,
            $parentUniverseId,
            $seedDna,
            $fitnessTotalScore,
            $lifespan
        );
    }

    /**
     * The Engine instructs the Universe to advance by 1 unit of time.
     */
    /**
     * The Engine instructs the Universe to advance by 1 unit of time.
     */
    public function advanceTick(
        StateVector $newStateVector,
        \WorldOS\Society\Faction\ValueObject\IdeologyVector $newIdeology,
        \WorldOS\Society\Culture\ValueObject\CulturalVector $newCulture,
        \WorldOS\Core\ValueObject\LifecycleState $newLifecycle,
        float $newInfluenceMass,
        int $newStabilityDuration
    ): void {
        if (!$this->status->canStep()) {
            throw new \DomainException("Cannot advance a universe that is not RUNNING.");
        }

        $this->currentTick++;
        $this->stateVector = $newStateVector;
        $this->ideology = $newIdeology;
        $this->culture = $newCulture;
        $this->lifecycle = $newLifecycle;
        $this->influenceMass = $newInfluenceMass;
        $this->stabilityDuration = $newStabilityDuration;
    }

    public function start(): void
    {
        $this->status = UniverseStatus::RUNNING;
    }

    public function collapse(int $finalTick): void
    {
        $this->status = UniverseStatus::COLLAPSED;
        $this->lifespan = $finalTick;
    }

    public function setFitnessScore(float $score): void
    {
        $this->fitnessTotalScore = $score;
    }

    // --- Getters ---

    public function getId(): UniverseId
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

    public function getWorldSignatureHash(): string
    {
        return $this->worldSignatureHash;
    }

    public function getStatus(): UniverseStatus
    {
        return $this->status;
    }

    public function getCurrentTick(): int
    {
        return $this->currentTick;
    }

    public function getEntropy(): float
    {
        return $this->stateVector->get(StateVector::DIMENSION_ENTROPY);
    }

    public function getStabilityIndex(): float
    {
        return $this->stateVector->get(StateVector::DIMENSION_STABILITY);
    }

    public function getStateVector(): StateVector
    {
        return $this->stateVector;
    }

    public function getIdeology(): \WorldOS\Society\Faction\ValueObject\IdeologyVector
    {
        return $this->ideology;
    }

    public function getCulture(): \WorldOS\Society\Culture\ValueObject\CulturalVector
    {
        return $this->culture;
    }

    public function getLifecycle(): \WorldOS\Core\ValueObject\LifecycleState
    {
        return $this->lifecycle;
    }

    public function getInfluenceMass(): float
    {
        return $this->influenceMass;
    }

    public function getStabilityDuration(): int
    {
        return $this->stabilityDuration;
    }

    public function getGenerationId(): ?string
    {
        return $this->generationId;
    }

    public function getParentUniverseId(): ?string
    {
        return $this->parentUniverseId;
    }

    public function getSeedDna(): array
    {
        return $this->seedDna;
    }

    public function getFitnessTotalScore(): ?float
    {
        return $this->fitnessTotalScore;
    }

    public function getMultiverseId(): string
    {
        return $this->multiverseId;
    }

    public function getLifespan(): ?int
    {
        return $this->lifespan;
    }
}
