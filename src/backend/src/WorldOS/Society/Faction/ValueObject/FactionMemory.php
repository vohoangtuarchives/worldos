<?php

declare(strict_types=1);

namespace WorldOS\Society\Faction\ValueObject;

/**
 * Faction memory: success score, war fatigue, myth backlash, intent history.
 */
readonly class FactionMemory
{
    public function __construct(
        public float $successScore = 0.0,
        public float $warFatigue = 0.0,
        public float $mythBacklash = 0.0,
        public array $intentHistory = [],
    ) {
    }

    public static function fresh(): self
    {
        return new self(0.0, 0.0, 0.0, []);
    }

    public function toArray(): array
    {
        return [
            'successScore' => $this->successScore,
            'warFatigue' => $this->warFatigue,
            'mythBacklash' => $this->mythBacklash,
            'intentHistory' => $this->intentHistory,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['successScore'] ?? 0.0),
            (float) ($data['warFatigue'] ?? 0.0),
            (float) ($data['mythBacklash'] ?? 0.0),
            $data['intentHistory'] ?? [],
        );
    }
}
