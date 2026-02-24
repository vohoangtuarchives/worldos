<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\ValueObjects;

/**
 * Evaluation Result — governance recommendation for a Universe.
 *
 * From docs §13.2: EvaluationResult (ip_score, recommendation, mutation_suggestion)
 */
final readonly class EvaluationResult
{
    public const CONTINUE = 'continue';
    public const FORK = 'fork';
    public const ARCHIVE = 'archive';

    public function __construct(
        public string $recommendation,    // CONTINUE, FORK, ARCHIVE
        public float $confidence,         // 0-1 how confident in this recommendation
        public string $reasoning,         // Human-readable explanation
        public ?string $mutationSuggestion = null, // Optional pressure suggestion
    ) {
    }

    public function shouldFork(): bool
    {
        return $this->recommendation === self::FORK;
    }

    public function shouldArchive(): bool
    {
        return $this->recommendation === self::ARCHIVE;
    }

    public function shouldContinue(): bool
    {
        return $this->recommendation === self::CONTINUE;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recommendation' => $this->recommendation,
            'confidence' => round($this->confidence, 4),
            'reasoning' => $this->reasoning,
            'mutation_suggestion' => $this->mutationSuggestion,
        ];
    }
}
