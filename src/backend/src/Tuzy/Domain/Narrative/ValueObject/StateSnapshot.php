<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

/**
 * Snapshot of narrative timeline state: active characters, flags, world tick.
 */
readonly class StateSnapshot
{
    public function __construct(
        public array $activeCharacterIds = [],
        public array $globalFlags = [],
        public int $worldTick = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['active_character_ids'] ?? [],
            $data['global_flags'] ?? [],
            (int) ($data['world_tick'] ?? 0),
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
