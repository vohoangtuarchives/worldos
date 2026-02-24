<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

/**
 * InteractionMemory — pairwise relationship memory between two attractors.
 * HR(A,B) = w₁·shared_survival - w₂·conflict_intensity + w₃·rebirth_alignment, |HR| ≤ 0.3
 */
final class InteractionMemory
{
    private const MAX_HR = 0.3;
    private const W_SHARED_SURVIVAL = 0.4;
    private const W_CONFLICT = 0.35;
    private const W_REBIRTH_ALIGNMENT = 0.25;

    public function __construct(
        public readonly string $attractorA,
        public readonly string $attractorB,
        public readonly float $sharedSurvival,
        public readonly float $conflictIntensity,
        public readonly float $rebirthAlignment,
        public readonly int $interactionCount,
    ) {}

    public static function initial(string $a, string $b): self
    {
        $sorted = [$a, $b];
        sort($sorted);
        return new self(
            attractorA: $sorted[0],
            attractorB: $sorted[1],
            sharedSurvival: 0.0,
            conflictIntensity: 0.0,
            rebirthAlignment: 0.0,
            interactionCount: 0,
        );
    }

    public function recordSharedSurvival(float $intensity = 0.1): self
    {
        return new self(
            attractorA: $this->attractorA,
            attractorB: $this->attractorB,
            sharedSurvival: min(1.0, $this->sharedSurvival + $intensity),
            conflictIntensity: $this->conflictIntensity,
            rebirthAlignment: $this->rebirthAlignment,
            interactionCount: $this->interactionCount + 1,
        );
    }

    public function recordConflict(float $intensity = 0.1): self
    {
        return new self(
            attractorA: $this->attractorA,
            attractorB: $this->attractorB,
            sharedSurvival: $this->sharedSurvival,
            conflictIntensity: min(1.0, $this->conflictIntensity + $intensity),
            rebirthAlignment: $this->rebirthAlignment,
            interactionCount: $this->interactionCount + 1,
        );
    }

    public function recordRebirthAlignment(float $alignment = 0.1): self
    {
        return new self(
            attractorA: $this->attractorA,
            attractorB: $this->attractorB,
            sharedSurvival: $this->sharedSurvival,
            conflictIntensity: $this->conflictIntensity,
            rebirthAlignment: min(1.0, $this->rebirthAlignment + $alignment),
            interactionCount: $this->interactionCount + 1,
        );
    }

    public function hrScore(): float
    {
        $raw = self::W_SHARED_SURVIVAL * $this->sharedSurvival
             - self::W_CONFLICT * $this->conflictIntensity
             + self::W_REBIRTH_ALIGNMENT * $this->rebirthAlignment;
        return max(-self::MAX_HR, min(self::MAX_HR, $raw));
    }

    public function forceModifier(): float
    {
        return 1.0 + $this->hrScore();
    }

    public function involves(string $code): bool
    {
        return $this->attractorA === $code || $this->attractorB === $code;
    }

    public function toArray(): array
    {
        return [
            'attractor_a' => $this->attractorA,
            'attractor_b' => $this->attractorB,
            'shared_survival' => $this->sharedSurvival,
            'conflict_intensity' => $this->conflictIntensity,
            'rebirth_alignment' => $this->rebirthAlignment,
            'hr_score' => $this->hrScore(),
            'interaction_count' => $this->interactionCount,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            attractorA: $data['attractor_a'],
            attractorB: $data['attractor_b'],
            sharedSurvival: $data['shared_survival'] ?? 0.0,
            conflictIntensity: $data['conflict_intensity'] ?? 0.0,
            rebirthAlignment: $data['rebirth_alignment'] ?? 0.0,
            interactionCount: $data['interaction_count'] ?? 0,
        );
    }
}
