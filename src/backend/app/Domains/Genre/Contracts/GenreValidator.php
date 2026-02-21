<?php

namespace App\Domains\Genre\Contracts;

use Tuzy\Domain\Genre\ValueObject\ValidationResult;
use Tuzy\Domain\Genre\ValueObject\StoryEvent;
// Assuming WorldState exists in Material domain, purely using dynamic/array for now to avoid coupling if specific class path unknown
// User mentioned WorldState, let's assume App\Domains\Material\State\WorldState if it exists, or just pass generic object
// To be safe, I'll typehint as mixed or object for now, or check file system. 
// Actually, I should check if WorldState exists. 

interface GenreValidator
{
    public function validateEvent(
        GenreDefinition $genre,
        object $worldState, // Typed as object to match any WorldState impl
        StoryEvent $event
    ): ValidationResult;
}
