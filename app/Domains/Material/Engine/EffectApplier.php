<?php

namespace App\Domains\Material\Engine;

use Illuminate\Support\Collection;

/**
 * EffectApplier - Component 4 of MaterialLawEngine
 * 
 * Purpose: Apply material effects as state deltas, NOT direct state mutation.
 * Rule: NO direct state modification. ALL changes via delta.
 */
class EffectApplier
{
    /**
     * Apply effects from active materials as state deltas.
     * 
     * @param Collection $activeMaterials
     * @param array $worldState Current state
     * @param float $deltaTime Time passed in this tick (in years)
     * @return array Deltas and origins
     */
    public function apply(Collection $activeMaterials, array $worldState, float $deltaTime = 1.0): array
    {
        $deltas = [];
        $origins = [];

        foreach ($activeMaterials as $instance) {
            if ($instance->retired_at) {
                continue; // Skip retired materials
            }

            $material = $instance->material;
            $strength = $instance->strength_level / 10.0; // Normalize to 0-1

            // Parse pressure_outputs
            $outputs = $material->pressure_outputs ?? [];
            
            foreach ($outputs as $target => $baseEffect) {
                // Scale effect by deltaTime
                $effectValue = $this->calculateEffectValue($baseEffect, $strength) * $deltaTime;
                
                // Aggregate deltas
                if (!isset($deltas[$target])) {
                    $deltas[$target] = 0.0;
                    $origins[$target] = [];
                }
                
                $deltas[$target] += $effectValue;
                $origins[$target][] = $material->code;
            }
        }

        // Clamp all deltas to reasonable bounds (Scaled by Time)
        foreach ($deltas as $key => $value) {
            $deltas[$key] = $this->clampDelta($value, $deltaTime);
        }

        return [
            'deltas' => $deltas,
            'origins' => $origins,
        ];
    }

    /**
     * Calculate effect value from base effect and material strength.
     */
    private function calculateEffectValue($baseEffect, float $strength): float
    {
        // If baseEffect is numeric, use it directly
        if (is_numeric($baseEffect)) {
            return (float) $baseEffect * $strength;
        }

        // If baseEffect is string, parse it
        if (is_string($baseEffect)) {
            // Handle formats like "+0.4", "-0.2", "0.5"
            if (preg_match('/^([+-]?)([\d.]+)$/', $baseEffect, $matches)) {
                $sign = $matches[1] === '-' ? -1 : 1;
                $value = (float) $matches[2];
                return $sign * $value * $strength;
            }
        }

        return 0.0;
    }

    /**
     * Clamp delta to prevent extreme state changes.
     * Max change per tick: ±0.3 (Scaled by Time)
     */
    private function clampDelta(float $delta, float $deltaTime = 1.0): float
    {
        $limit = 0.3 * $deltaTime;
        return min($limit, max(-$limit, $delta));
    }

    /**
     * Apply deltas to world state and return new state.
     * This enforces 0.0 - 1.0 bounds.
     */
    public function commitDeltas(array $currentState, array $deltas): array
    {
        $newState = $currentState;

        foreach ($deltas as $key => $delta) {
            $current = $currentState[$key] ?? 0.5; // Default to neutral
            $newValue = $current + $delta;
            
            // Clamp to [0.0, 1.0]
            $newState[$key] = min(1.0, max(0.0, $newValue));
        }

        return $newState;
    }
}
