<?php

declare(strict_types=1);

namespace WorldOS\Core\ValueObject;

/**
 * SubstrateVector: The hidden influence of the Substrate Law.
 * Deterministic modifiers that affect physics and evolution.
 * Immutable.
 */
readonly class SubstrateVector
{
    public function __construct(
        public float $entropyDissipation,      // Khả năng tự ổn định của vũ trụ
        public float $birthPressure,           // Hệ số sinh sản văn minh
        public float $mutationIntensity,       // Cường độ biến dị văn hóa & tư tưởng
        public float $fragilityModifier,       // Hệ số giòn của các cấu trúc lớn
        public float $transcendenceAccess,     // Khả năng tiếp cận các chiều siêu việt
        public array $driftSeeds = [],         // Các hạt giống nhiễu deterministic cho mutation/drift
    ) {
    }

    public static function null(): self
    {
        return new self(1.0, 1.0, 1.0, 1.0, 1.0, [0, 0, 0, 0]);
    }

    public function toArray(): array
    {
        return [
            'entropy_dissipation' => $this->entropyDissipation,
            'birth_pressure'      => $this->birthPressure,
            'mutation_intensity' => $this->mutationIntensity,
            'fragility_modifier' => $this->fragilityModifier,
            'transcendence_access' => $this->transcendenceAccess,
        ];
    }
}
