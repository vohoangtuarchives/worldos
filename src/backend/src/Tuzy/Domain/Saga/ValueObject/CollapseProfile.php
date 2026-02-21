<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\ValueObject;

/**
 * Structured abstraction for collapse (for AI input).
 */
readonly class CollapseProfile
{
    public function __construct(
        public float $severity,
        public string $collapseType,
        public string $dominantContradiction = '',
    ) {
    }

    public static function fromCauseAndState(string $cause, array $finalState): self
    {
        $entropy = (float) ($finalState['entropy'] ?? 0.5);
        $severity = min(1.0, $entropy + (str_contains(strtolower($cause), 'structural') ? 0.2 : 0));
        $collapseType = self::inferType($cause, $finalState);
        $dominantContradiction = self::inferContradiction($collapseType);
        return new self($severity, $collapseType, $dominantContradiction);
    }

    private static function inferType(string $cause, array $finalState): string
    {
        if (str_contains(strtolower($cause), 'structural') || str_contains(strtolower($cause), 'fracture')) {
            return 'structural_fracture';
        }
        if (str_contains(strtolower($cause), 'entropy') || (($finalState['entropy'] ?? 0) > 0.9)) {
            return 'entropy_overload';
        }
        if (($finalState['inequality'] ?? 0) > 0.7) {
            return 'inequality_revolt';
        }
        return 'unknown';
    }

    private static function inferContradiction(string $collapseType): string
    {
        return match ($collapseType) {
            'inequality_revolt' => 'inequality_vs_legitimacy',
            'entropy_overload' => 'entropy_vs_order',
            'structural_fracture' => 'cohesion_vs_stress',
            default => 'instability',
        };
    }
}
