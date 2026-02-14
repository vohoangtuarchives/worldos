<?php

namespace App\Narrative\Values;

use App\Domains\Genre\Contracts\GenreDefinition;

class NarrativeContext
{
    public function __construct(
        public readonly string $targetLanguage,
        public readonly string $tone,
        public readonly string $audience,
        public readonly ?GenreDefinition $genre = null,
        public readonly ?\App\Domains\Social\HonorificContext $socialContext = null,
        public readonly ?\App\Domains\Power\Enums\PowerStage $powerStage = null,
        public readonly ?\App\Domains\Genre\Signal\GenreProfile $genreProfile = null,
        public readonly ?\App\Domains\Saga\Enums\PowerScope $powerScope = null,
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
