<?php

namespace App\Domains\Faction\ValueObjects;

class PersonalityVector
{
    public function __construct(
        public float $aggression,    // 0.0 - 1.0 (Peaceful - Warlike)
        public float $rationality,   // 0.0 - 1.0 (Impulsive - Strategic)
        public float $faith,         // 0.0 - 1.0 (Skeptic - Zealot)
        public float $fear,          // 0.0 - 1.0 (Brave - Paranoid)
        public float $ambition,      // 0.0 - 1.0 (Content - Megalomaniac)
    ) {}

    public static function random(): self
    {
        return new self(
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100)
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
            $data['aggression'] ?? 0.5,
            $data['rationality'] ?? 0.5,
            $data['faith'] ?? 0.5,
            $data['fear'] ?? 0.5,
            $data['ambition'] ?? 0.5
        );
    }

    /**
     * Inherit personality from another vector with mutation.
     */
    public function inherit(PersonalityVector $parent, float $variance = 0.1): self
    {
        return new self(
            $this->mutate($parent->aggression, $variance),
            $this->mutate($parent->rationality, $variance),
            $this->mutate($parent->faith, $variance),
            $this->mutate($parent->fear, $variance),
            $this->mutate($parent->ambition, $variance)
        );
    }

    private function mutate(float $value, float $variance): float
    {
        $change = (mt_rand(-100, 100) / 100) * $variance;
        return max(0.0, min(1.0, $value + $change));
    }
}
