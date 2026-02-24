<?php

declare(strict_types=1);

namespace WorldOS\Society\Faction\ValueObject;

/**
 * Leader personality dimensions (0.0–1.0 each).
 */
readonly class PersonalityVector
{
    public function __construct(
        public float $aggression = 0.5,
        public float $rationality = 0.5,
        public float $faith = 0.5,
        public float $fear = 0.5,
        public float $ambition = 0.5,
    ) {
    }

    public static function random(): self
    {
        return new self(
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
        );
    }

    public function toArray(): array
    {
        return [
            'aggression' => $this->aggression,
            'rationality' => $this->rationality,
            'faith' => $this->faith,
            'fear' => $this->fear,
            'ambition' => $this->ambition,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['aggression'] ?? 0.5),
            (float) ($data['rationality'] ?? 0.5),
            (float) ($data['faith'] ?? 0.5),
            (float) ($data['fear'] ?? 0.5),
            (float) ($data['ambition'] ?? 0.5),
        );
    }

    public function inherit(self $parent, float $variance = 0.1): self
    {
        return new self(
            $this->clamp($parent->aggression + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($parent->rationality + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($parent->faith + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($parent->fear + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($parent->ambition + (mt_rand(-100, 100) / 100) * $variance),
        );
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
