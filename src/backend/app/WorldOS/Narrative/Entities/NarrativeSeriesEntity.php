<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Entities;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\ValueObjects\GenreKey;
use LogicException;
use Ramsey\Uuid\Uuid;

/**
 * Narrative Series Entity — container for a story within a Universe.
 *
 * From docs §11.2: One Universe, Many Series.
 * State: current_book_index, total_chapters_generated.
 * Config: quality_pipeline, require_arc_approval.
 *
 * Hierarchy: Saga → Book → Arc → Chapter.
 * Pure PHP — NO Eloquent.
 */
final class NarrativeSeriesEntity
{
    /**
     * @param string     $id
     * @param UniverseId $universeId
     * @param GenreKey   $genre
     * @param string     $title
     * @param int        $currentBookIndex
     * @param int        $totalChaptersGenerated
     * @param bool       $requireArcApproval
     * @param bool       $isActive
     */
    public function __construct(
        private readonly string $id,
        private readonly UniverseId $universeId,
        private readonly GenreKey $genre,
        private string $title,
        private int $currentBookIndex,
        private int $totalChaptersGenerated,
        private bool $requireArcApproval,
        private bool $isActive,
    ) {
    }

    public static function create(
        UniverseId $universeId,
        GenreKey $genre,
        string $title,
        bool $requireArcApproval = true,
    ): self {
        return new self(
            id: Uuid::uuid4()->toString(),
            universeId: $universeId,
            genre: $genre,
            title: $title,
            currentBookIndex: 1,
            totalChaptersGenerated: 0,
            requireArcApproval: $requireArcApproval,
            isActive: true,
        );
    }

    // ── Getters ──

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCurrentBookIndex(): int
    {
        return $this->currentBookIndex;
    }

    public function getTotalChaptersGenerated(): int
    {
        return $this->totalChaptersGenerated;
    }

    public function requiresArcApproval(): bool
    {
        return $this->requireArcApproval;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    // ── Business Methods ──

    public function advanceBook(): void
    {
        if (!$this->isActive) {
            throw new LogicException("Cannot advance inactive series [{$this->id}]");
        }

        $this->currentBookIndex++;
    }

    public function recordChapterGenerated(): void
    {
        $this->totalChaptersGenerated++;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
