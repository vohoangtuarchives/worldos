<?php

namespace App\Domains\Genre\Contracts;

interface VocabularyMap
{
    /**
     * Translate a generic concept to genre specific term.
     */
    public function term(string $key): string;
    
    /**
     * Get all mappings.
     */
    public function all(): array;
}
