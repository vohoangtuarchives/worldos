<?php

use App\Models\World;
use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Cosmology\Services\BasePhysicsEngine;
use App\Domains\Saga\Services\EntropyPressureService;
use App\Domains\Saga\Services\CivilizationScorer;
use App\Domains\Saga\Services\BlueprintMutationPlanner;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n--- SIMULATION: WORLD vs UNIVERSE & MULTIVERSE INTERACTION ---\n\n";

// 1. GENOTYPE: The World (Class/Blueprints)
echo "[1] GENOTYPE (The World)\n";
$world = new World([
    'name' => 'Gaia Prime',
    'physics_profile' => [
        'entropy_rate' => 0.05, // Normal entropy
        'complexity_bias' => 0.5
    ]
]);
echo "Created World '{$world->name}' with Physics Profile: " . json_encode($world->physics_profile->toArray()) . "\n\n";

// 2. PHENOTYPE: Spawning Universes (Instances/Objects)
echo "[2] PHENOTYPE (The Universes)\n";
$initialState = WorldStateVector::create(
    order: 0.8,
    entropy: 0.1, // Clean slate
    cohesion: 0.9,
    legitimacy: 1.0,
    innovation: 0.5,
    military: 0.0
);

// Universe A: The "Peaceful" Timeline
$universeA = new Universe($initialState, [], 'uni-alpha-001', 0);
echo "Spawned Universe A (Alpha) - Initial State: Entropy 0.1, Order 0.8\n";

// Universe B: The "Chaotic" Timeline (Forked/Divergent)
$universeB = new Universe($initialState, [], 'uni-beta-002', 0);
echo "Spawned Universe B (Beta)  - Initial State: Entropy 0.1, Order 0.8\n\n";

// 3. EVOLUTION: Divergence over Time
echo "[3] EVOLUTION (Divergence)\n";
$physicsEngine = app(BasePhysicsEngine::class);

// Evolve A peacefully
$universeA->applyMutation(WorldStateVector::fromArray(['innovation' => 0.1])); // Small boost
echo "Universe A evolves peacefully...\n";

// Evolve B with WAR
$warImpact = WorldStateVector::fromArray([
    'military' => 0.8,
    'entropy' => 0.6, // Significant entropy spike
    'order' => -0.4
]);
$universeB->applyMutation($warImpact);
echo "Universe B suffers a Catastrophic War...\n";

echo "State A Entropy: {$universeA->getState()->getEntropy()}\n";
echo "State B Entropy: {$universeB->getState()->getEntropy()}\n\n";

// 4. INTERACTION: Saga Pressure (Hydraulics)
echo "[4] INTERACTION (Saga Pressure)\n";
// Manually calculate pressure since Service works on Models, but logic is simple
$pressureService = app(EntropyPressureService::class);
// Mocking World Models for the service (as it expects models)
$mockWorldA = new World(['entropy' => $universeA->getState()->getEntropy()]);
$mockWorldB = new World(['entropy' => $universeB->getState()->getEntropy()]);

$pressureBA = $pressureService->calculatePressure($mockWorldB, $mockWorldA); // B -> A
echo "Calculated Entropy Pressure (Beta -> Alpha): {$pressureBA}\n";

if ($pressureBA > 0.7) {
    echo "ALERT: CRITICAL PRESSURE! Risk of Reality Invasion (Terraform Event).\n";
} elseif ($pressureBA > 0.3) {
    echo "NOTICE: High Pressure. Inter-dimensional Rift opens. Entropy flows from Beta to Alpha.\n";
    // Simulate Flow
    $flow = $pressureBA * 0.1;
    echo "-> Flowing {$flow} entropy from B to A...\n";
} else {
    echo "Status: Stable separation.\n";
}
echo "\n";

// 5. FEEDBACK: Updating the Genotype
echo "[5] FEEDBACK (Updating Genotype)\n";
// Assume Universe B collapses due to high entropy
echo "Universe B collapses due to Entropy Overload.\n";
$scorer = app(CivilizationScorer::class);
$report = $scorer->scoreFromCollapse('entropy_overload', $universeB->getState()->getAll());

echo "Civilization Report for Beta:\n";
echo " - Stability Score: {$report->stabilityScore}\n";
echo " - Resilience Index: {$report->resilienceIndex}\n";
echo " - Collapse Cause: {$report->collapseType}\n";

$planner = app(BlueprintMutationPlanner::class);
$blueprint = $planner->planFromReport($report);

echo "Blueprint Mutation for Next Generation (New World Genotype):\n";
print_r($blueprint['mutation_bias']);

echo "\n--- SIMULATION COMPLETE ---\n";
