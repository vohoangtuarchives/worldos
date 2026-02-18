<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\ValueObjects;

/**
 * Phase transition signal from CriticalityDetector / BasePhysicsEngine.
 * World layer uses this to decide collapse (StructuralMutationEngine) vs reorganize.
 */
final readonly class PhaseSignal
{
    public function __construct(
        public string $phase,
        public ?string $zone,
        public float $pressure,
        public bool $shouldCollapse,
        public bool $canReorganize,
        public ?float $cascadeSeverity = null,
    ) {
    }

    public static function fromAssessment(array $assessment): self
    {
        return new self(
            phase: $assessment['phase'] ?? 'STABLE',
            zone: $assessment['zone'] ?? null,
            pressure: (float) ($assessment['pressure'] ?? 0.0),
            shouldCollapse: (bool) ($assessment['should_collapse'] ?? false),
            canReorganize: (bool) ($assessment['can_reorganize'] ?? false),
            cascadeSeverity: isset($assessment['cascade_severity']) ? (float) $assessment['cascade_severity'] : null,
        );
    }
}
