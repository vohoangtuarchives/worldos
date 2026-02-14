<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\ValueObjects;

final readonly class IntelligenceReport
{
    public function __construct(
        public readonly string $id,
        public readonly IntelligenceType $type,
        public readonly IntelligenceSource $source,
        public readonly string $content,
        public readonly array $metadata,
        public readonly \DateTime $timestamp,
        public readonly float $accuracy,
        public readonly int $age,
    ) {}

    public function isReliable(): bool
    {
        return $this->source->reliability > 0.7 && $this->accuracy > 0.7;
    }

    public function isRecent(int $maxAge = 10): bool
    {
        return $this->age < $maxAge;
    }

    public function isExpired(int $maxAge = 50): bool
    {
        return $this->age > $maxAge;
    }

    public function getEffectiveAccuracy(): float
    {
        // Combine source reliability with report accuracy
        return ($this->source->reliability * 0.6) + ($this->accuracy * 0.4);
    }

    public function getUrgency(): string
    {
        return match (true) {
            $this->metadata['urgency'] ?? null === 'high' => 'high',
            $this->metadata['risk_level'] ?? null === 'elevated' => 'medium',
            $this->type === IntelligenceType::EVENT_ANALYSIS => 'medium',
            default => 'low'
        };
    }

    public function withAge(int $newAge): self
    {
        return new self(
            $this->id,
            $this->type,
            $this->source,
            $this->content,
            $this->metadata,
            $this->timestamp,
            $this->accuracy,
            $newAge
        );
    }

    public function withDecayedAccuracy(float $decayRate): self
    {
        $newAccuracy = max(0.1, $this->accuracy - $decayRate);
        
        return new self(
            $this->id,
            $this->type,
            $this->source,
            $this->content,
            $this->metadata,
            $this->timestamp,
            $newAccuracy,
            $this->age
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'source' => $this->source->toArray(),
            'content' => $this->content,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'accuracy' => $this->accuracy,
            'age' => $this->age,
            'effective_accuracy' => $this->getEffectiveAccuracy(),
            'urgency' => $this->getUrgency(),
            'is_reliable' => $this->isReliable(),
            'is_recent' => $this->isRecent(),
            'is_expired' => $this->isExpired(),
        ];
    }
}
