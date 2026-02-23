<?php

namespace WorldOS\Legacy\Domain\Genre\Contracts;

use WorldOS\Legacy\Domain\Genre\ValueObject\ValidationResult;
use WorldOS\Legacy\Domain\Genre\ValueObject\StoryEvent;
// Assuming WorldState exists in Material domain, purely using dynamic/array for now to avoid coupling if specific class path unknown
// User mentioned WorldState, let's assume WorldOS\Legacy\Application\Material\State\WorldState if it exists, or just pass generic object
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
