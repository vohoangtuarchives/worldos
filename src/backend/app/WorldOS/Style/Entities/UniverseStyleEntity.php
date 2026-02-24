<?php

declare(strict_types=1);

namespace App\WorldOS\Style\Entities;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\Style\ValueObjects\GenreKey;
use App\WorldOS\Style\ValueObjects\StyleVector;
use LogicException;
use Ramsey\Uuid\Uuid;

/**
 * Universe Style Entity — defines the narrative "physics" overlay.
 *
 * From docs §15.1: UniverseStyle(world_id, style_vector, name, version, is_active)
 *
 * A style modifies the base LawVector to create genre-specific physics.
 * For example, Xianxia style cranks up selfOrganization and mythFormation
 * while lowering techAccumulationRate.
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class UniverseStyleEntity
{
    /**
     * @param string      $id
     * @param UniverseId  $universeId
     * @param GenreKey    $genre
     * @param StyleVector $styleVector
     * @param string      $name        e.g., "Azure Dragon Sect Era"
     * @param int         $version     Monotonically increasing
     * @param bool        $isActive    Only one style active per Universe
     */
    public function __construct(
        private readonly string $id,
        private readonly UniverseId $universeId,
        private readonly GenreKey $genre,
        private StyleVector $styleVector,
        private string $name,
        private int $version,
        private bool $isActive,
    ) {
    }

    public static function create(
        UniverseId $universeId,
        GenreKey $genre,
        string $name,
        ?StyleVector $customVector = null,
    ): self {
        return new self(
            id: Uuid::uuid4()->toString(),
            universeId: $universeId,
            genre: $genre,
            styleVector: $customVector ?? $genre->defaultStyleVector(),
            name: $name,
            version: 1,
            isActive: true,
        );
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): string
    {
        return $this->id;
    }

    public function getUniverseId(): UniverseId
    {
        return $this->universeId;
    }

    public function getGenre(): GenreKey
    {
        return $this->genre;
    }

    public function getStyleVector(): StyleVector
    {
        return $this->styleVector;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Calculate the physics overlay — how this style modifies the base LawVector.
     *
     * Genre style creates biases on the 17D law space:
     * - High ontology (magic) → boosts selfOrganization, mythFormation, reduces techAccumulation
     * - High epistemic (mystical) → boosts memoryPersistence, reduces causalityRigidity
     * - High energy → boosts interactionStrength, reduces collapseProbability
     * - High civilization → boosts cognitiveCeiling, techAccumulation
     */
    public function calculatePhysicsOverlay(LawVector $baseLaw): LawVector
    {
        $sv = $this->styleVector;
        $data = $baseLaw->toArray();

        // Ontology axis (magic↔tech)
        $data['self_organization'] = $this->blend($data['self_organization'], $sv->ontology, 0.3);
        $data['myth_formation'] = $this->blend($data['myth_formation'], $sv->ontology, 0.4);
        $data['tech_accumulation_rate'] = $this->blend($data['tech_accumulation_rate'], 1.0 - $sv->ontology, 0.25);

        // Epistemic axis (empirical↔mystical)
        $data['memory_persistence'] = $this->blend($data['memory_persistence'], $sv->epistemic, 0.3);
        $data['causality_rigidity'] = $this->blend($data['causality_rigidity'], 1.0 - $sv->epistemic, 0.2);

        // Energy axis
        $data['interaction_strength'] = $this->blend($data['interaction_strength'], $sv->energy, 0.3);
        $data['collapse_probability'] = $this->blend($data['collapse_probability'], 1.0 - $sv->energy, 0.2);

        // Civilization axis
        $data['cognitive_ceiling'] = $this->blend($data['cognitive_ceiling'], $sv->civilization, 0.25);
        $data['meta_system_awareness'] = $this->blend($data['meta_system_awareness'], $sv->civilization, 0.2);

        return LawVector::fromArray($data);
    }

    /**
     * Deactivate this style.
     */
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /**
     * Evolve style vector (for StyleAdvisorService — future).
     */
    public function evolveStyle(StyleVector $newVector): void
    {
        if (!$this->isActive) {
            throw new LogicException("Cannot evolve inactive style [{$this->id}]");
        }

        $this->styleVector = $newVector;
        $this->version++;
    }

    // ──────────────────────────────────────────
    // Private
    // ──────────────────────────────────────────

    /**
     * Blend a base value toward a target with given influence strength.
     * result = base × (1 - strength) + target × strength
     */
    private function blend(float $base, float $target, float $strength): float
    {
        $result = $base * (1.0 - $strength) + $target * $strength;

        return max(0.0, min(1.0, $result));
    }
}
