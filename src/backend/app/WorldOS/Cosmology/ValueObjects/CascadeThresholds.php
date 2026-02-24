<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\ValueObjects;

use InvalidArgumentException;

/**
 * Cascade Thresholds — τ₁-τ₄ activation thresholds for layer emergence.
 *
 * Each threshold defines the minimum value of the lower layer
 * required for the upper layer to begin emerging:
 *   τ₁: Physics → Chemistry
 *   τ₂: Chemistry → Biology
 *   τ₃: Biology → Cognition
 *   τ₄: Cognition → Culture
 */
final readonly class CascadeThresholds
{
    public function __construct(
        public float $physicsToChemistry,   // τ₁
        public float $chemistryToBiology,   // τ₂
        public float $biologyCognition,     // τ₃
        public float $cognitionToCulture,   // τ₄
    ) {
        $this->validateAll();
    }

    /**
     * Default thresholds derived from simulation design docs.
     */
    public static function defaults(): self
    {
        return new self(
            physicsToChemistry: 0.3,
            chemistryToBiology: 0.4,
            biologyCognition: 0.5,
            cognitionToCulture: 0.6,
        );
    }

    /**
     * @param array<string, float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            physicsToChemistry: (float) ($data['physics_to_chemistry'] ?? 0.3),
            chemistryToBiology: (float) ($data['chemistry_to_biology'] ?? 0.4),
            biologyCognition: (float) ($data['biology_to_cognition'] ?? 0.5),
            cognitionToCulture: (float) ($data['cognition_to_culture'] ?? 0.6),
        );
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'physics_to_chemistry' => $this->physicsToChemistry,
            'chemistry_to_biology' => $this->chemistryToBiology,
            'biology_to_cognition' => $this->biologyCognition,
            'cognition_to_culture' => $this->cognitionToCulture,
        ];
    }

    /**
     * Get threshold by layer index (0-based: 0=P→C, 1=C→B, 2=B→N, 3=N→K).
     */
    public function getThreshold(int $index): float
    {
        return match ($index) {
            0 => $this->physicsToChemistry,
            1 => $this->chemistryToBiology,
            2 => $this->biologyCognition,
            3 => $this->cognitionToCulture,
            default => throw new InvalidArgumentException(
                "Threshold index must be 0-3, got: {$index}"
            ),
        };
    }

    private function validateAll(): void
    {
        $thresholds = $this->toArray();

        foreach ($thresholds as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new InvalidArgumentException(
                    "Cascade threshold '{$name}' must be in [0.0, 1.0], got: {$value}"
                );
            }
        }
    }
}
