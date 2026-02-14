<?php

namespace App\Domains\Genre\DTO;

class GenrePromptCapsule
{
    public function __construct(
        public string $systemPrompt,
        public array $forbiddenConcepts,
        public array $allowedOverrides
    ) {}
}
