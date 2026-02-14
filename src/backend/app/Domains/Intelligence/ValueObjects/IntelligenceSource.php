<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\ValueObjects;

final readonly class IntelligenceSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
        public readonly float $reliability,
    ) {
        if ($reliability < 0.0 || $reliability > 1.0) {
            throw new \InvalidArgumentException('Reliability must be between 0 and 1');
        }
    }

    public static function character(string $characterId, float $reliability = 0.7): self
    {
        return new self('character', $characterId, $reliability);
    }

    public static function environment(string $worldId, float $reliability = 0.9): self
    {
        return new self('environment', $worldId, $reliability);
    }

    public static function event(string $eventId, float $reliability = 0.8): self
    {
        return new self('event', $eventId, $reliability);
    }

    public static function faction(string $factionId, float $reliability = 0.6): self
    {
        return new self('faction', $factionId, $reliability);
    }

    public static function myth(string $mythId, float $reliability = 0.5): self
    {
        return new self('myth', $mythId, $reliability);
    }

    public function isReliable(): bool
    {
        return $this->reliability > 0.7;
    }

    public function getTrustLevel(): string
    {
        return match (true) {
            $this->reliability >= 0.8 => 'high',
            $this->reliability >= 0.6 => 'medium',
            $this->reliability >= 0.4 => 'low',
            default => 'unreliable'
        };
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'reliability' => $this->reliability,
            'trust_level' => $this->getTrustLevel(),
            'is_reliable' => $this->isReliable(),
        ];
    }
}
