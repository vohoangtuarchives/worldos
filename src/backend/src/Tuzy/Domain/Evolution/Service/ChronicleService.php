<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\ChronicleEvent;
use Tuzy\Domain\Evolution\Service\Extractors\PhaseTransitionExtractor;
use Tuzy\Domain\Evolution\Service\Extractors\FactionDynamicsExtractor;
use Tuzy\Domain\Evolution\Service\Extractors\EcologicalCrisisExtractor;
use Tuzy\Domain\Evolution\Service\Extractors\HeroicEmergenceExtractor;

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
