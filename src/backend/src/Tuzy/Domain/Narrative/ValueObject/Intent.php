<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

/**
 * Dialogue intent: type, payload, confidence, optional target character.
 */
readonly class Intent
{
    public function __construct(
        public string $type,
        public array $payload = [],
        public float $confidence = 1.0,
        public ?string $targetCharacterId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'] ?? '',
            $data['payload'] ?? [],
            (float) ($data['confidence'] ?? 1.0),
            $data['target_character_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'payload' => $this->payload,
            'confidence' => $this->confidence,
            'target_character_id' => $this->targetCharacterId,
        ];
    }
}
