<?php

namespace WorldOS\Legacy\Application\CivilizationDynamics\Services;

use WorldOS\Legacy\Application\CivilizationDynamics\Entities\CivilizationState;
use WorldOS\Legacy\Application\WorldEvolution\Entities\WorldState;
use WorldOS\Blueprint\Domain\LegacyEvolution\Events\WorldEvent;

class InternalEventGenerator
{
    private InternalPressureCalculator $calculator;
    private const CRITICAL_THRESHOLD = 0.85;

    public function __construct(InternalPressureCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Inspect pressures and generate an event if a critical threshold is crossed.
     * Throws an event randomly based on the highest pressure peak.
     */
    public function inspectAndGenerate(CivilizationState $civ, WorldState $world): ?WorldEvent
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

        // Stochastic trigger check: Higher pressure past threshold = very high chance of event
        if ($highestPressure >= self::CRITICAL_THRESHOLD) {
            $triggerChance = ($highestPressure - self::CRITICAL_THRESHOLD) / (1.0 - self::CRITICAL_THRESHOLD);
            if (lcg_value() < $triggerChance + 0.1) { // Add 10% base chance beyond threshold
                return $this->createEventFromPressure($highestType, $civ, $world);
            }
        }

        return null;
    }

    private function createEventFromPressure(string $type, CivilizationState $civ, WorldState $world): WorldEvent
    {
        // Define an anonymous event class mapping the pressure eruption
        return new class($world->sagaId, $world->universeId, $world->currentYear, $type, $civ->name) extends WorldEvent {
            private string $pType;
            private string $civName;

            public function __construct($sId, $uId, $y, $pType, $civName) {
                parent::__construct($sId, $uId, $y, 0.8);
                $this->pType = $pType;
                $this->civName = $civName;
            }

            public function getName(): string {
                return "Bùng nổ áp lực: " . ucwords(str_replace('_', ' ', $this->pType)) . " tại " . $this->civName;
            }

            public function getImpactVector(): array {
                // Return generic destabilization impact
                return [
                    \WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector::DIMENSION_ENTROPY => +0.15,
                    \WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector::DIMENSION_ORDER => -0.1
                ];
            }
        };
    }
}
