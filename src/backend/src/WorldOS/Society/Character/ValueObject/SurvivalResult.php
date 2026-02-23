<?php

declare(strict_types=1);

namespace WorldOS\Society\Character\ValueObject;

/**
 * Result of a survival check (DTO): character id, probability, survived flag, reason.
 * Use this in Tuzy/API boundaries; App SurvivalCheckEngine can build it from CharacterSurvivalAggregate.
 */
readonly class SurvivalResult
{
    public function __construct(
        public string $characterId,
        public float $probability,
        public bool $survived,
        public string $reason,
    ) {
    }

    public static function survived(string $characterId, float $probability, string $reason): self
    {
        return new self($characterId, $probability, true, $reason);
    }

    public static function died(string $characterId, float $probability, string $reason): self
    {
        return new self($characterId, $probability, false, $reason);
    }

    public function toArray(): array
    {
        return [
            'character_id' => $this->characterId,
            'survived' => $this->survived,
            'probability' => $this->probability,
            'reason' => $this->reason,
        ];
    }
}
