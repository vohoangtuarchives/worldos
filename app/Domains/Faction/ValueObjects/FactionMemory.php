<?php

namespace App\Domains\Faction\ValueObjects;

class FactionMemory
{
    public function __construct(
        public float $successScore,     // Accumulated success rate
        public float $warFatigue,      // Accumulated negative impact of war
        public float $mythBacklash,    // Accumulated penalty from failed myths
        public array $intentHistory = [], // Log of recent intents and outcomes
    ) {}

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
            $data['intentHistory'] ?? []
        );
    }
}
