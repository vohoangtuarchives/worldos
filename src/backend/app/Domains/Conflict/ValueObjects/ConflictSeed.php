<?php

declare(strict_types=1);

namespace App\Domains\Conflict\ValueObjects;

/**
 * Value object: one conflict potential derived from structural state.
 * Output of StructuralInterpreter; input for ArcSelector / StoryStructureEngine.
 */
final readonly class ConflictSeed
{
    public const TYPE_CLASS_STRUGGLE = 'class_struggle';
    public const TYPE_ELITE_POWER_CONSOLIDATION = 'elite_power_consolidation';
    public const TYPE_INSTITUTIONAL_FRAGILITY = 'institutional_fragility';
    public const TYPE_REBELLION_POTENTIAL = 'rebellion_potential';

    public const STABILITY_VOLATILE = 'volatile';
    public const STABILITY_STABLE = 'stable';
    public const STABILITY_BUILDING = 'building';

    public function __construct(
        public string $type,
        public float $intensity,
        public string $stability,
    ) {
        if ($intensity < 0.0 || $intensity > 1.0) {
            throw new \InvalidArgumentException('intensity must be in [0, 1]');
        }
        $validStability = [self::STABILITY_VOLATILE, self::STABILITY_STABLE, self::STABILITY_BUILDING];
        if (!in_array($stability, $validStability, true)) {
            throw new \InvalidArgumentException('stability must be one of: ' . implode(', ', $validStability));
        }
    }

    public static function classStruggle(float $intensity, string $stability = self::STABILITY_BUILDING): self
    {
        return new self(self::TYPE_CLASS_STRUGGLE, $intensity, $stability);
    }

    public static function elitePowerConsolidation(float $intensity, string $stability = self::STABILITY_BUILDING): self
    {
        return new self(self::TYPE_ELITE_POWER_CONSOLIDATION, $intensity, $stability);
    }

    public static function institutionalFragility(float $intensity, string $stability = self::STABILITY_BUILDING): self
    {
        return new self(self::TYPE_INSTITUTIONAL_FRAGILITY, $intensity, $stability);
    }

    public static function rebellionPotential(float $intensity, string $stability = self::STABILITY_BUILDING): self
    {
        return new self(self::TYPE_REBELLION_POTENTIAL, $intensity, $stability);
    }
}
