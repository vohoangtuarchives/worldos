<?php

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\Universe;
use Tuzy\Domain\Evolution\ValueObject\WorldStateVector;
use App\Models\UniverseModel;

class InterventionService
{
    public const TYPE_STABILIZE = 'STABILIZE';
    public const TYPE_DISRUPT = 'DISRUPT';

    /** Severity â†’ strength multiplier for narrative injection */
    private const SEVERITY_STRENGTH = [
        'LOW' => 0.05,
        'MEDIUM' => 0.12,
        'HIGH' => 0.22,
        'CALAMITY' => 0.35,
    ];

    /**
     * Inject narrative event into universe â€” applies DISRUPT-style state changes.
     * Stores last_injected_event for "Canonize Last Turn".
     */
    public function injectNarrative(UniverseModel $model, string $content, string $severity): void
    {
        $strength = self::SEVERITY_STRENGTH[$severity] ?? 0.1;
        $sv = $model->state_vector ?? [];

        $order = ($sv['order'] ?? 0.5) - $strength;
        $entropy = min(1.0, ($sv['entropy'] ?? 0.5) + $strength);
        $cohesion = max(0, ($sv['cohesion'] ?? 0.5) - ($strength * 0.8));
        $inequality = min(1.0, ($sv['inequality'] ?? 0) + ($strength * 0.5));
        $trauma = min(1.0, ($sv['trauma'] ?? 0) + ($strength * 0.4));
        $innovation = min(1.0, ($sv['innovation'] ?? 0.5) + ($strength * 0.2));

        $model->state_vector = array_merge($sv, [
            'order' => max(0, min(1, $order)),
            'entropy' => $entropy,
            'cohesion' => $cohesion,
            'inequality' => $inequality,
            'trauma' => $trauma,
            'innovation' => $innovation,
        ]);

        $params = $model->parameters ?? [];
        $params['last_injected_event'] = [
            'content' => $content,
            'severity' => $severity,
            'epoch' => $model->age,
            'timestamp' => now()->toIso8601String(),
        ];
        $params['injected_events'] = array_merge($params['injected_events'] ?? [], [
            ['content' => $content, 'severity' => $severity, 'epoch' => $model->age, 'timestamp' => now()->toIso8601String()],
        ]);
        $model->parameters = $params;
        $model->save();
    }

    public function intervene(Universe $universe, string $type, float $strength = 0.1): Universe
    {
        $state = $universe->getState();
        
        $newOrder = $state->getOrder();
        $newEntropy = $state->getEntropy();
        $newCohesion = $state->getCohesion();
        $newInequality = $state->getInequality();
        $newTrauma = $state->getTrauma();
        $newInnovation = $state->getInnovation();
        
        if ($type === self::TYPE_STABILIZE) {
            // "The Weaver mends the strands."
            // Increase Order, Cohesion.
            // Decrease Entropy, Trauma.
            $newOrder = min(1.0, $newOrder + $strength);
            $newCohesion = min(1.0, $newCohesion + ($strength * 0.8));
            $newEntropy = max(0.0, $newEntropy - ($strength * 1.2));
            $newTrauma = max(0.0, $newTrauma - ($strength * 0.5));
            
            // Side effect: Stagnation might rise if Order is too high, but let's keep it simple.
        } elseif ($type === self::TYPE_DISRUPT) {
            // "The Weaver tears the fabric."
            // Increase Entropy, Inequality, Innovation (Chaos breeds invention).
            // Decrease Order, Cohesion.
            $newEntropy = min(1.0, $newEntropy + $strength);
            $newOrder = max(0.0, $newOrder - ($strength * 1.0));
            $newInequality = min(1.0, $newInequality + ($strength * 0.5));
            $newInnovation = min(1.0, $newInnovation + ($strength * 0.3));
            $newCohesion = max(0.0, $newCohesion - ($strength * 0.8));
        }

        $newVector = WorldStateVector::create(
            $newOrder,
            $newEntropy,
            $newCohesion,
            $state->getLegitimacy(),
            $newInnovation,
            $state->getMilitary(),
            $newInequality,
            $newTrauma,
            $state->getEliteCohesion(),
            $state->getResourceStock()
        );

        // Return new Universe instance (Immutable pattern)
        return new Universe(
            $newVector,
            $universe->getHistory(), // We should append to history log ideally
            $universe->getId(),
            $universe->getAge()
        );
    }
}



