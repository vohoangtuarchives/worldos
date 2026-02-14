<?php

namespace App\Domains\Narrative\Dialogue\ValueObjects;

class Intent
{
    public function __construct(
        public readonly string $type, // e.g., 'PROBE', 'REVEAL', 'DEFLECT', 'EMOTIONAL_PRESSURE'
        public readonly array $payload, // e.g., ['fact' => 'Sky is blue']
        public readonly float $confidence, // 0.0 to 1.0 (how sure the character is)
        public readonly ?string $targetCharacterId = null // Who is this directed at?
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'],
            $data['payload'] ?? [],
            (float) ($data['confidence'] ?? 1.0),
            $data['target_character_id'] ?? null
        );
    }
}
