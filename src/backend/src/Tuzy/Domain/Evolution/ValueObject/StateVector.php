<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\ValueObject;

use InvalidArgumentException;

/**
 * StateVector - Represents the 17 dimensions of a civilization as a mathematical vector.
 */
class StateVector extends \Tuzy\Domain\Math\Vector
{
    public const DIMENSIONS = 17;

    public const KEYS = [
        'ce', 'sc', 'tech', 'stability', 'prosperity', 'mp', 'ie',
        'legitimacy', 'eliteCohesion', 'inequality',
        'sustainability', 'mystery', 'legacy',
        'expansion', 'info', 'mobility', 'curvature'
    ];

    /**
     * @param float[] $values Indexed 0 to 16, or associative keys matching self::KEYS
     */
    public function __construct(array $values) 
    {
        // Convert sequential array to associative if necessary
        $components = [];
        if (array_is_list($values) && count($values) === self::DIMENSIONS) {
            foreach (self::KEYS as $index => $key) {
                $components[$key] = $values[$index];
            }
        } else {
            foreach (self::KEYS as $key) {
                $components[$key] = $values[$key] ?? 0.0;
            }
        }
        
        // Prepare base vector
        parent::__construct($components);
        
        // Saturate components to [-1, 1] using tanh
        $this->components = array_map(fn($v) => tanh($v), $this->components);
    }

    public static function fromSnapshot(CivilizationSnapshot $snapshot): self
    {
        return new self([
            'ce' => $snapshot->culturalEnergy,
            'sc' => $snapshot->spiritualCohesion,
            'tech' => $snapshot->technologicalLevel,
            'stability' => $snapshot->stability,
            'prosperity' => $snapshot->prosperity,
            'mp' => $snapshot->militaryPressure,
            'ie' => $snapshot->internalEntropy,
            'legitimacy' => $snapshot->legitimacy,
            'eliteCohesion' => $snapshot->eliteCohesion,
            'inequality' => $snapshot->inequality,
            'sustainability' => $snapshot->sustainability,
            'mystery' => $snapshot->mystery,
            'legacy' => $snapshot->historicalLegacy,
            'expansion' => $snapshot->expansionism,
            'info' => $snapshot->informationFlow,
            'mobility' => $snapshot->socialMobility,
            'curvature' => $snapshot->fieldCurvature,
        ]);
    }

    /**
     * Helper to return associative array matching old logic
     */
    public function toAssocArray(): array
    {
        return $this->components;
    }
    
    /**
     * Keep the public $values property synced for backward compatibility
     */
    public function __get(string $name)
    {
        if ($name === 'values') {
            return array_values($this->components);
        }
        return null;
    }
}
