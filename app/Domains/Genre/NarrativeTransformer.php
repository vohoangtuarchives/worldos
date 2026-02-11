<?php

namespace App\Domains\Genre;

use App\Domains\Genre\Contracts\GenreDefinition;
use App\Domains\Genre\Contracts\VocabularyMap;

class NarrativeTransformer
{
    public function __construct(
        private GenreRegistry $registry
    ) {}

    public function transform(string $narrative, string $genreKey): string
    {
        $genre = $this->registry->get($genreKey);
        
        if (!$genre) {
            return $narrative; // Fallback to raw history
        }
        
        return $this->applySimpleTransformation($narrative, $genre);
    }
    
    private function applySimpleTransformation(string $text, GenreDefinition $genre): string
    {
        $vocab = $genre->vocabulary();
        $transformed = $text;
        
        // Iterate through all terms in the vocabulary map
        foreach ($vocab->all() as $genericTerm => $genreTerm) {
            // Case-insensitive replacement
            // Use word boundaries \b to avoid partial matches if possible, 
            // but for simple terms like 'school' -> 'Sect', we want fairly broad matching
            $pattern = '/\b' . preg_quote($genericTerm, '/') . '\b/i';
            $transformed = preg_replace($pattern, $genreTerm, $transformed);
        }
        
        // Prepend stylistic marker
        return "[{$genre->displayName()}] " . $transformed;
    }
}

