<?php

namespace Tuzy\Application\CognitiveKernel\Drift;

use Tuzy\Domain\CognitiveKernel\ArchetypeWeight;
use App\Models\World;
use Illuminate\Support\Collection;

/**
 * Drift Calculator
 * 
 * Calculates archetype drift based on 4 sources:
 * 1. Repetition Pressure - Overuse loses meaning
 * 2. Trauma Residue - Legacy creates bias
 * 3. Power Capture - Elite monopolize interpretation
 * 4. Absence Pressure - Suppression causes overshoot
 * 
 * Constitutional Constraint (ADR-1002):
 * Drift is slow, continuous, reversible, from history
 */
class DriftCalculator
{
    private RepetitionPressure $repetitionPressure;
    private TraumaResidue $traumaResidue;
    private PowerCapture $powerCapture;
    private AbsencePressure $absencePressure;

    public function __construct()
    {
        $this->repetitionPressure = new RepetitionPressure();
        $this->traumaResidue = new TraumaResidue();
        $this->powerCapture = new PowerCapture();
        $this->absencePressure = new AbsencePressure();
    }

    /**
     * Calculate total drift for an archetype in a world
     * 
     * @return array ['delta' => float, 'sources' => array]
     */
    public function calculate(
        World $world,
        ArchetypeWeight $archetypeWeight,
        ?array $context = null
    ): array {
        $context = $context ?? $this->buildContext($world);

        // Calculate drift from each source
        $repetition = $this->repetitionPressure->calculate($world, $archetypeWeight, $context);
        $trauma = $this->traumaResidue->calculate($world, $archetypeWeight, $context);
        $power = $this->powerCapture->calculate($world, $archetypeWeight, $context);
        $absence = $this->absencePressure->calculate($world, $archetypeWeight, $context);

        // Total drift
        $totalDelta = $repetition + $trauma + $power + $absence;

        // Apply volatility from archetype definition
        $archetype = $archetypeWeight->archetype();
        if ($archetype) {
            $totalDelta *= $archetype->volatility;
        }

        // Clamp drift to reasonable bounds per tick (-0.1 to +0.1)
        $totalDelta = max(-0.1, min(0.1, $totalDelta));

        return [
            'delta' => $totalDelta,
            'sources' => [
                'repetition' => $repetition,
                'trauma' => $trauma,
                'power' => $power,
                'absence' => $absence,
            ]
        ];
    }

    /**
     * Calculate drift for all archetypes in a world
     */
    public function calculateAll(World $world): Collection
    {
        $weights = ArchetypeWeight::where('world_id', $world->id)->get();
        $context = $this->buildContext($world);

        return $weights->map(function ($weight) use ($world, $context) {
            $drift = $this->calculate($world, $weight, $context);
            
            return [
                'archetype_key' => $weight->archetype_key,
                'current_weight' => $weight->weight,
                'drift' => $drift,
                'new_weight' => max(0, min(1, $weight->weight + $drift['delta']))
            ];
        });
    }

    /**
     * Build context from world state for drift calculation
     */
    private function buildContext(World $world): array
    {
        return [
            'world_id' => $world->id,
            'tick' => $world->tick ?? 0,
            'myths' => $world->myths()->count(),
            'scars' => $world->scars()->count(),
            'events' => $world->events()->count(),
            // Add more context as needed
        ];
    }
}
