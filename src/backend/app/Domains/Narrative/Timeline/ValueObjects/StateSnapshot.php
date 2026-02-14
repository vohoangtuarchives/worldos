<?php

namespace App\Domains\Narrative\Timeline\ValueObjects;

class StateSnapshot
{
    public function __construct(
        public readonly array $activeCharacterIds,
        public readonly array $globalFlags,
        public readonly int $worldTick
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['active_character_ids'] ?? [],
            $data['global_flags'] ?? [],
            $data['world_tick'] ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'active_character_ids' => $this->activeCharacterIds,
            'global_flags' => $this->globalFlags,
            'world_tick' => $this->worldTick,
        ];
    }
}
