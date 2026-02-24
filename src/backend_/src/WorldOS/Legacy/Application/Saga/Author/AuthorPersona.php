<?php

namespace WorldOS\Legacy\Application\Saga\Author;

/**
 * AuthorPersona - Defines the unique 'voice' of a writer.
 */
class AuthorPersona
{
    public function __construct(
        public readonly string $name,
        public readonly string $tone,
        public readonly array $vocabularyMap = [],
        public readonly array $introStyles = [],
        public readonly array $bridgingPhrases = [],
        public readonly array $signatureFlourishes = [],
        public readonly array $descriptors = []
    ) {}

    /**
     * Transform a target string using author-specific vocabulary.
     */
    public function stylize(string $text): string
    {
        $transformed = $text;
        foreach ($this->vocabularyMap as $generic => $specific) {
            $pattern = '/\b' . preg_quote($generic, '/') . '\b/i';
            $transformed = preg_replace($pattern, $specific, $transformed);
        }
        return $transformed;
    }

    /**
     * Get all signature flourishes.
     */
    public function getFlourishes(): array
    {
        return $this->signatureFlourishes;
    }

    /**
     * Get a random signature flourish to sprinkle into the prose.
     */
    public function getRandomFlourish(): ?string
    {
        if (empty($this->signatureFlourishes)) return null;
        return $this->signatureFlourishes[array_rand($this->signatureFlourishes)];
    }
}
