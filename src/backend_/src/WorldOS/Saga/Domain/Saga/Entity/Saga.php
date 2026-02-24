<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Saga\Entity;

use InvalidArgumentException;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;
use WorldOS\Saga\Domain\Saga\ValueObject\LifecyclePhase;
use WorldOS\Saga\Domain\Saga\ValueObject\SagaMetrics;

/**
 * Saga Aggregate Root — The central entity coordinating the canonical narrative timeline
 * of a specific Universe.
 *
 * A Saga wraps the Hero journey, the Macro Myth trajectory, and tracks the lifecycle 
 * across up to 300+ chapters.
 */
class Saga
{
    /**
     * @param array<int, string> $arcIds
     */
    private function __construct(
        private readonly string         $id,
        private readonly string         $universeId,
        private HeroState               $heroState,
        private MythVector              $mythVector,
        private LifecyclePhase          $lifecyclePhase,
        private SagaMetrics             $metrics,
        private int                     $chapterCount,
        private array                   $arcIds,
        private ?string                 $attractorType = null
    ) {
    }

    public static function spawn(
        string         $id,
        string         $universeId,
        HeroState      $initialHero,
        MythVector     $initialMyth
    ): self {
        return new self(
            $id,
            $universeId,
            $initialHero,
            $initialMyth,
            LifecyclePhase::SEED,
            SagaMetrics::genesis(),
            0,
            []
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUniverseId(): string
    {
        return $this->universeId;
    }

    public function getHeroState(): HeroState
    {
        return $this->heroState;
    }

    public function getMythVector(): MythVector
    {
        return $this->mythVector;
    }

    public function getLifecyclePhase(): LifecyclePhase
    {
        return $this->lifecyclePhase;
    }

    public function getMetrics(): SagaMetrics
    {
        return $this->metrics;
    }

    public function getChapterCount(): int
    {
        return $this->chapterCount;
    }

    public function getArcHistory(): array
    {
        return $this->arcIds;
    }

    public function getAttractorType(): ?string
    {
        return $this->attractorType;
    }

    /**
     * Evolves the internal state of the Saga.
     */
    public function evolveInternalState(
        HeroState $nextHeroState,
        MythVector $nextMythVector,
        LifecyclePhase $nextPhase,
        SagaMetrics $nextMetrics,
        bool $incrementChapter
    ): void {
        $this->heroState = $nextHeroState;
        $this->mythVector = $nextMythVector;
        $this->lifecyclePhase = $nextPhase;
        $this->metrics = $nextMetrics;

        if ($incrementChapter) {
            $this->chapterCount++;
        }
    }

    public function appendArc(string $arcId): void
    {
        $this->arcIds[] = $arcId;
    }

    public function concludeWithAttractor(string $attractorType): void
    {
        if ($this->lifecyclePhase === LifecyclePhase::ARCHIVED) {
            throw new InvalidArgumentException("Cannot change attractor on an archived saga.");
        }
        
        $this->attractorType = $attractorType;
    }

    public function archive(): void
    {
        $this->lifecyclePhase = LifecyclePhase::ARCHIVED;
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'universe_id'     => $this->universeId,
            'lifecycle_phase' => $this->lifecyclePhase->value,
            'chapter_count'   => $this->chapterCount,
            'attractor_type'  => $this->attractorType,
            'metrics'         => $this->metrics->toArray(),
            'hero_state'      => $this->heroState->toArray(),
            'myth_vector'     => $this->mythVector->toArray(),
            'arc_ids'         => $this->arcIds,
        ];
    }
}
