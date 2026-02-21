<?php

namespace Tuzy\Domain\Saga;

use Tuzy\Application\Material\State\WorldState;
use Tuzy\Application\Material\State\SagaLens;

/**
 * SagaInterpreter - Read WorldState through narrative lenses
 * 
 * Detects state changes and calculates deltas.
 */
class SagaInterpreter
{
    public function __construct(
        private SagaLens $lens
    ) {}

    /**
     * Interpret state changes through saga lens.
     * 
     * @param WorldState $previousState
     * @param WorldState $currentState
     * @param string $sagaType 'structural' | 'symbolic' | 'interaction' | 'full'
     * @return array State changes with deltas
     */
    public function interpret(
        WorldState $previousState,
        WorldState $currentState,
        string $sagaType = 'full'
    ): array {
        $prevView = $this->lens->read($previousState, $sagaType);
        $currView = $this->lens->read($currentState, $sagaType);

        return $this->detectChanges($prevView, $currView);
    }

    /**
     * Detect changes between two state views.
     */
    private function detectChanges(array $prev, array $curr): array
    {
        $changes = [];

        foreach ($curr as $key => $value) {
            if (is_array($value)) {
                $subChanges = $this->detectChanges($prev[$key] ?? [], $value);
                if (!empty($subChanges)) {
                    $changes[$key] = $subChanges;
                }
            } elseif (($prev[$key] ?? null) !== $value) {
                $changes[$key] = [
                    'from' => $prev[$key] ?? null,
                    'to' => $value,
                    'delta' => is_numeric($value) && is_numeric($prev[$key] ?? 0)
                        ? round($value - ($prev[$key] ?? 0), 4)
                        : null,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get significant changes (delta > threshold).
     */
    public function getSignificantChanges(array $changes, float $threshold = 0.1): array
    {
        $significant = [];

        foreach ($changes as $key => $change) {
            if (is_array($change) && isset($change['delta'])) {
                if (abs($change['delta']) >= $threshold) {
                    $significant[$key] = $change;
                }
            } elseif (is_array($change)) {
                $subSignificant = $this->getSignificantChanges($change, $threshold);
                if (!empty($subSignificant)) {
                    $significant[$key] = $subSignificant;
                }
            }
        }

        return $significant;
    }
}
