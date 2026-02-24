<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Attractor\ValueObject;

use InvalidArgumentException;

/**
 * AttractorResult — The computed output evaluating which gravitational ending pulled the hardest.
 * Immutable Value Object.
 */
final class AttractorResult
{
    /**
     * @param AttractorType $type The winning attractor
     * @param array<string, float> $affinityScores Scores for each attractor [type => score]
     */
    private function __construct(
        private readonly AttractorType $type,
        private readonly array $affinityScores
    ) {
    }

    public static function create(AttractorType $type, array $affinityScores): self
    {
        foreach (AttractorType::cases() as $case) {
            if (!isset($affinityScores[$case->value])) {
                throw new InvalidArgumentException("Missing affinity score for attractor: {$case->value}");
            }
        }

        return new self($type, $affinityScores);
    }

    public function getType(): AttractorType
    {
        return $this->type;
    }

    public function getAffinityScores(): array
    {
        return $this->affinityScores;
    }

    public function getScoreFor(AttractorType $type): float
    {
        return $this->affinityScores[$type->value] ?? 0.0;
    }

    public function toArray(): array
    {
        return [
            'type'            => $this->type->value,
            'affinity_scores' => $this->affinityScores,
        ];
    }
}
