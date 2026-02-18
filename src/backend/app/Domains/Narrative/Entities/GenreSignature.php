<?php

namespace App\Domains\Narrative\Entities;

class GenreSignature
{
    public const GENRE_UTOPIAN = 'utopian';
    public const GENRE_DYSTOPIAN = 'dystopian';
    public const GENRE_WAR = 'war';
    public const GENRE_ELDRITCH = 'eldritch';
    public const GENRE_RENAISSANCE = 'renaissance';
    public const GENRE_CYBERPUNK = 'cyberpunk'; // High Innovation + Low Cohesion?
    public const GENRE_DECAY = 'decay';

    private string $primaryGenre;
    private array $traits;

    public function __construct(string $primaryGenre, array $traits = [])
    {
        $this->primaryGenre = $primaryGenre;
        $this->traits = $traits;
    }

    public function getPrimaryGenre(): string
    {
        return $this->primaryGenre;
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function __toString(): string
    {
        return sprintf(
            "Genre: %s (Traits: %s)",
            strtoupper($this->primaryGenre),
            implode(', ', $this->traits)
        );
    }
}
