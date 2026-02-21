<?php

namespace App\Narrative\Values;

use Tuzy\Domain\Genre\Contracts\GenreDefinition;

class NarrativeContext
{
    public function __construct(
        public readonly string $targetLanguage,
        public readonly string $tone,
        public readonly string $audience,
        public readonly ?GenreDefinition $genre = null,
        public readonly ?\Tuzy\Domain\Social\HonorificContext $socialContext = null,
        public readonly ?\Tuzy\Domain\Power\ValueObject\PowerStage $powerStage = null,
        public readonly ?\Tuzy\Domain\Genre\ValueObject\GenreProfile $genreProfile = null,
        public readonly ?\Tuzy\Domain\Saga\Enums\PowerScope $powerScope = null,
        public readonly string $phase = 'stable' // pre, moment, post
    ) {}

    public static function default(): self
    {
        return new self(
            targetLanguage: 'vi',
            tone: 'han-viet',
            audience: 'human_reader'
        );
    }
}
