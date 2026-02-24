<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\ValueObjects;

use App\WorldOS\Attractor\Entities\AttractorEntity;
use App\WorldOS\CivilizationMemory\Entities\WorldMythEntity;
use App\WorldOS\CivilizationMemory\Entities\WorldScarEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\LawVector;

/**
 * EvolutionContext — context passed to each influence for evaluation.
 *
 * Provides influences with everything they need to calculate their force,
 * without coupling them to repositories or services.
 */
final readonly class EvolutionContext
{
    /**
     * @param WorldScarEntity[] $scars
     * @param WorldMythEntity[] $activeMyths
     * @param AttractorEntity[] $activeAttractors
     */
    public function __construct(
        public UniverseId $universeId,
        public int $tick,
        public LawVector $lawVector,
        public array $scars = [],
        public array $activeMyths = [],
        public array $activeAttractors = [],
        public float $scarPressure = 0.0,
    ) {
    }
}
