<?php

declare(strict_types=1);

namespace Tuzy\Domain\Genre\ValueObject;

/**
 * Genre prompt payload: system prompt, forbidden concepts, allowed overrides.
 */
readonly class GenrePromptCapsule
{
    public function __construct(
        public string $systemPrompt,
        public array $forbiddenConcepts = [],
        public array $allowedOverrides = [],
    ) {
    }
}
