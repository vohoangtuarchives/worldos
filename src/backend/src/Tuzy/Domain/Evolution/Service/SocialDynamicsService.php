<?php

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\SocialClass;
use Tuzy\Domain\Evolution\Enum\SocialClassType;

class SocialDynamicsService
{
    /**
     * Calculate the evolution of social classes over a period of time.
     * 
     * @param SocialClass[] $currentClasses
     * @param CivilizationSnapshot $civ
     * @param CosmicState $cosmic
     * @param int $years
     * @return SocialClass[]
     */
    public function evolveClasses(array $currentClasses, CivilizationSnapshot $civ, CosmicState $cosmic, int $years): array
    {
        $newClasses = [];
        $totalPowerShift = 0.0;

        foreach ($currentClasses as $class) {
            $powerChange = $this->calculatePowerChange($class, $civ, $cosmic, $years);
            $contentmentChange = $this->calculateContentmentChange($class, $civ, $cosmic, $years);
            
            $newPower = max(0.01, min(1.0, $class->power + $powerChange));
            $newContentment = max(0.0, min(1.0, $class->contentment + $contentmentChange));
            
            $newClasses[] = new SocialClass(
                $class->type,
                $newPower,
                $newContentment,
                $class->size // Size shifts are more complex, keep stable for now
            );
        }

        return $newClasses;
    }

    private function calculatePowerChange(SocialClass $class, CivilizationSnapshot $civ, CosmicState $cosmic, int $years): float
    {
        $rate = 0.0001 * $years; // Base rate per year
        $change = 0.0;

        switch ($class->type) {
            case SocialClassType::MERCHANT:
                // Merchants thrive on Technology and relative Stability
                $change += ($civ->technologicalLevel * 0.5) * $rate;
                if ($cosmic->entropy < 0.3) $change += 0.1 * $rate;
                break;

            case SocialClassType::PRIESTHOOD:
                // Priesthood thrives on Spiritual Cohesion but loses to Knowledge
                $change += ($civ->spiritualCohesion * 0.6) * $rate;
                $change -= ($civ->culturalEnergy * 0.4) * $rate;
                break;

            case SocialClassType::NOBILITY:
                // Nobility thrives on Stability and low Entropy
                $change += ($civ->stability * 0.5) * $rate;
                $change -= ($cosmic->entropy * 0.3) * $rate;
                break;

            case SocialClassType::WARRIOR:
                // Warriors thrive on Entropy (chaos/need for security) and Faction instability
                $change += ($cosmic->entropy * 0.6) * $rate;
                $change += (1.0 - $civ->stability) * 0.4 * $rate;
                break;

            case SocialClassType::INTELLECTUAL:
                // Intellectuals thrive on Knowledge
                $change += ($civ->culturalEnergy * 0.8) * $rate;
                break;

            case SocialClassType::PEASANTRY:
                // Peasantry power is usually low but can drift upwards with Resilience
                $change += ($civ->resilience * 0.1) * $rate;
                break;
        }

        return $change;
    }

    private function calculateContentmentChange(SocialClass $class, CivilizationSnapshot $civ, CosmicState $cosmic, int $years): float
    {
        $rate = 0.0005 * $years;
        $change = 0.0;

        // General effects
        if ($cosmic->entropy > 0.7) $change -= 0.5 * $rate; // High entropy depresses everyone
        if ($civ->resilience < 0.3) $change -= 0.3 * $rate;  // System fatigue hurts morale

        switch ($class->type) {
            case SocialClassType::PEASANTRY:
                // Contentment drops with inequality (nobility/merchant power)
                $inequality = 0;
                foreach ($civ->socialClasses as $c) {
                    if ($c->type === SocialClassType::NOBILITY || $c->type === SocialClassType::MERCHANT) {
                        $inequality += $c->power;
                    }
                }
                if ($inequality > 1.2) $change -= 0.2 * $rate;
                break;

            case SocialClassType::WARRIOR:
                // Happy when entropy is high (work!) but sad if tech makes them obsolete
                if ($cosmic->entropy > 0.5) $change += 0.1 * $rate;
                if ($civ->technologicalLevel > 1.5) $change -= 0.2 * $rate;
                break;
        }

        return $change;
    }
}




