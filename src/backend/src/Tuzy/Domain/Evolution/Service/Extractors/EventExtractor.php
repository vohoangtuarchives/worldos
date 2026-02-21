<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service\Extractors;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\ChronicleEvent;

/**
 * EventExtractor - Interface for all history event extractors.
 */
interface EventExtractor
{
    /**
     * Extracts chronicle events by comparing the previous state with the current state.
     *
     * @param array $prevFactions Pre-tick factions array
     * @param array $currFactions Post-tick factions array
     * @param array $prevClusters Pre-tick static clusters array
     * @param array $currClusters Post-tick static clusters array
     * @return ChronicleEvent[]
     */
    public function extract(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array;
}
