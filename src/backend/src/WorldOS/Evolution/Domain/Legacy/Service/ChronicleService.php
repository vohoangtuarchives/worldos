<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\ChronicleEvent;
use WorldOS\Evolution\Domain\Legacy\Service\Extractors\PhaseTransitionExtractor;
use WorldOS\Evolution\Domain\Legacy\Service\Extractors\FactionDynamicsExtractor;
use WorldOS\Evolution\Domain\Legacy\Service\Extractors\EcologicalCrisisExtractor;
use WorldOS\Evolution\Domain\Legacy\Service\Extractors\HeroicEmergenceExtractor;

/**
 * ChronicleService - Runs all extractors to generate history logs natively.
 */
final class ChronicleService
{
    private array $extractors;

    public function __construct()
    {
        $this->extractors = [
            new PhaseTransitionExtractor(),
            new FactionDynamicsExtractor(),
            new EcologicalCrisisExtractor(),
            new HeroicEmergenceExtractor(),
        ];
    }

    /**
     * Compare previous state with current state and extract key events.
     *
     * @return ChronicleEvent[]
     */
    public function generateLogs(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array
    {
        $events = [];

        foreach ($this->extractors as $extractor) {
            $extracted = $extractor->extract($prev, $curr);
            foreach ($extracted as $e) {
                $events[] = $e;
            }
        }

        return $events;
    }
}
