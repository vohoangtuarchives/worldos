<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\ValueObjects;

use InvalidArgumentException;

/**
 * StateVector - Represents the 17 dimensions of a civilization as a mathematical vector.
 */
class StateVector
{
    public const DIMENSIONS = 17;

    public const KEYS = [
        'ce', 'sc', 'tech', 'stability', 'prosperity', 'mp', 'ie',
        'legitimacy', 'eliteCohesion', 'inequality',
        'sustainability', 'mystery', 'legacy',
        'expansion', 'info', 'mobility', 'curvature'
    ];

    /**
     * @param float[] $values Indexed 0 to 16
     */
    public function __construct(
        public array $values
    ) {
        if (count($values) !== self::DIMENSIONS) {
            throw new InvalidArgumentException("StateVector must have exactly " . self::DIMENSIONS . " dimensions.");
        }
    }

    public static function fromSnapshot(CivilizationSnapshot $snapshot): self
    {
        return new self([
            $snapshot->culturalEnergy,
            $snapshot->spiritualCohesion,
            $snapshot->technologicalLevel,
            $snapshot->stability,
            $snapshot->prosperity,
            $snapshot->militaryPressure,
            $snapshot->internalEntropy,
            $snapshot->legitimacy,
            $snapshot->eliteCohesion,
            $snapshot->inequality,
            $snapshot->sustainability,
            $snapshot->mystery,
            $snapshot->historicalLegacy,
            $snapshot->expansionism,
            $snapshot->informationFlow,
            $snapshot->socialMobility,
            $snapshot->fieldCurvature,
        ]);
    }

    /**
     * Tái tạo lại array map để dễ dàng gán vào các thuộc tính
     */
    public function toAssocArray(): array
    {
        $assoc = [];
        foreach (self::KEYS as $index => $key) {
            $assoc[$key] = $this->values[$index];
        }
        return $assoc;
    }
}
