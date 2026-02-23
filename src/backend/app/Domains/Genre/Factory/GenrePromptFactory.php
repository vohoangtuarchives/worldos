<?php

namespace App\Domains\Genre\Factory;

use App\Domains\Genre\Contracts\GenreDefinition;
use WorldOS\Legacy\Domain\Genre\ValueObject\GenrePromptCapsule;

class GenrePromptFactory
{
    public static function fromGenre(GenreDefinition $genre): GenrePromptCapsule
    {
        return new GenrePromptCapsule(
            systemPrompt: sprintf(
                "You are writing in the %s genre.\nPower comes from %s.\nProgression stages: %s.\nDeath is irreversible unless specified by world laws.",
                $genre->displayName(),
                $genre->materials()->primary(),
                implode(', ', $genre->progression()->stages())
            ),
            forbiddenConcepts: [
                'HP bar',
                'cooldown',
                'respawn',
                'modern UI',
                'science-fiction technology',
            ],
            allowedOverrides: [
                'artifact',
                'tribulation',
                'reincarnation',
            ]
        );
    }
}
