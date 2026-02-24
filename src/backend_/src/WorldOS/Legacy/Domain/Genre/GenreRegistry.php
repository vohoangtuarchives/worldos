<?php

namespace WorldOS\Legacy\Domain\Genre;

use WorldOS\Legacy\Domain\Genre\Contracts\GenreDefinition;

class GenreRegistry
{
    /** @var array<string, GenreDefinition> */
    private array $genres = [];

    public function register(GenreDefinition $genre): void
    {
        $this->genres[$genre->key()] = $genre;
    }

    public function get(string $key): ?GenreDefinition
    {
        return $this->genres[$key] ?? null;
    }

    /**
     * Get all registered genres.
     * @return array<string, GenreDefinition>
     */
    public function all(): array
    {
        return $this->genres;
    }
}

