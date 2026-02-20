<?php

namespace WorldOS\Domains\Evolution\Services;
use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;

use WorldOS\Domains\Evolution\CivilizationSnapshot;
use WorldOS\Domains\Evolution\WorldState;
use WorldOS\Domains\Evolution\Events\CatastropheErupted;

class InternalEventGenerator
{
    private const CRITICAL_THRESHOLD = 0.70;

    public function __construct(
        private InternalPressureCalculator $calculator
    ) {}

    public function inspectAndGenerate(CivilizationSnapshot $civ, WorldState $world): ?CatastropheErupted
    {
        $pressures = $this->calculator->calculatePressure($civ, $world);
        
        $highestPressure = 0.0;
        $highestType = null;

        foreach ($pressures as $type => $magnitude) {
            if ($magnitude > $highestPressure) {
                $highestPressure = $magnitude;
                $highestType = $type;
            }
        }

        // Stochastic trigger check: Higher pressure past threshold = chance of event
        if ($highestPressure >= self::CRITICAL_THRESHOLD) {
            $triggerChance = ($highestPressure - self::CRITICAL_THRESHOLD) / (1.0 - self::CRITICAL_THRESHOLD);
            if (lcg_value() < $triggerChance + 0.1) {
                return new CatastropheErupted(
                    $world->getId(), 
                    $civ->getId(), 
                    $highestType, 
                    new \DateTimeImmutable()
                );
            }
        }

        return null;
    }
}




