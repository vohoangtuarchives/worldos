<?php

use App\Models\World;
use App\Models\Faction;
use WorldOS\World\Application\Services\WorldEventLedger;
use App\Domains\World\Services\StageTransitionEngine;
use App\Domains\Material\MaterialArchetypeCoupler;
use App\Domains\Material\Material;
use App\Domains\Material\MaterialInstance;
use App\Domains\Power\PowerStageRegistry;
use Illuminate\Support\Facades\Artisan;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Setup Fresh World
echo "--- STEP 1: Setting up World ---\n";
$world = World::updateOrCreate(
    ['name' => 'Ledger Test World'],
    [
        'type' => \Tuzy\Domain\World\Enums\WorldType::WUXIA,
        'config' => ['current_stage' => 'mundane'],
        'tick' => 1
    ]
);

$faction = Faction::updateOrCreate(
    ['world_id' => $world->id, 'name' => 'Test Empire'],
    [
        'type' => 'clan',
        'internal_cohesion' => 1.0
    ]
);

// 2. Seed Materials with Stage Requirements
echo "--- STEP 2: Seeding Stage-Restricted Materials ---\n";
$bureaucracy = Material::updateOrCreate(
    ['code' => 'BUREAUCRATIC_EMPIRE'],
    [
        'ontology' => 'behavioral',
        'function' => 'legitimizing',
        'default_lifecycle' => 'active',
        'preconditions' => ['stage >= 1'] // Needs Mortal Martial
    ]
);

$divineKingship = Material::updateOrCreate(
    ['code' => 'DIVINE_KINGSHIP'],
    [
        'ontology' => 'symbolic',
        'function' => 'transformative',
        'default_lifecycle' => 'dormant',
        'preconditions' => ['stage >= 3'] // Needs Low Immortal (Stage 3)
    ]
);

$instanceBureau = MaterialInstance::create([
    'world_id' => $world->id,
    'material_id' => $bureaucracy->id,
    'strength_level' => 5
]);

$instanceDivine = MaterialInstance::create([
    'world_id' => $world->id,
    'material_id' => $divineKingship->id,
    'strength_level' => 5
]);

// 2.5 Seed Archetype Weights
echo "--- STEP 2.5: Seeding Archetype Weights ---\n";
$archetypes = ['authority', 'order', 'sacred'];
foreach ($archetypes as $key) {
    \Illuminate\Support\Facades\DB::table('archetype_weights')->updateOrInsert(
        ['world_id' => $world->id, 'archetype_key' => $key],
        ['weight' => 0.9, 'id' => \Illuminate\Support\Str::uuid()]
    );
}

// 3. Verify Initial State
$ledger = new WorldEventLedger();
$registry = new PowerStageRegistry();
$engine = new StageTransitionEngine($ledger, $registry);
$coupler = app(MaterialArchetypeCoupler::class);

echo "Initial Stage: " . ($world->config['current_stage'] ?? 'mundane') . "\n";
echo "Initial Pressure: " . $ledger->calculateGlobalPressure($world) . "\n";

// 4. record high-magnitude events to push pressure
echo "--- STEP 3: Recording Heavy Ledger Events ---\n";
$ledger->record($world, 'great_war', 'A world-shaping war happened.', 0.5, 1.0);
$ledger->record($world, 'spiritual_leak', 'Ancient seals are cracking.', 0.3, 1.0);

$pressure = $ledger->calculateGlobalPressure($world);
echo "New Pressure: $pressure\n";

// 5. Trigger Transition
echo "--- STEP 4: Evaluating Transition ---\n";
$transitioned = $engine->evaluateTransition($world);
$world->refresh();

echo "Transition Success: " . ($transitioned ? 'YES' : 'NO') . "\n";
echo "New Stage: " . $world->config['current_stage'] . "\n";

// 6. Verify Material Activation Logic
echo "--- STEP 5: Verifying Material Activation Logic ---\n";

// Mock archetype weights so they exceed thresholds if stage allowed
$mockArchetypes = ['sacred' => 0.9, 'martial' => 0.9]; 

// Test Bureaucracy (Needs Stage 1)
$canActivateBureau = this_checkActivation($coupler, $world, $instanceBureau); 
echo "Bureaucracy can activate in " . $world->config['current_stage'] . ": " . ($canActivateBureau ? 'YES' : 'NO') . "\n";

// Test Divine Kingship (Needs stage 3, we are at stage 1 or 2)
$canActivateDivine = this_checkActivation($coupler, $world, $instanceDivine);
echo "Divine Kingship can activate in " . $world->config['current_stage'] . ": " . ($canActivateDivine ? 'YES' : 'NO') . "\n";

// 7. Push to Mythic for fun
echo "--- STEP 6: Pushing to High Immortal ---\n";
$ledger->record($world, 'ascension', 'The heavens have opened.', 10.0, 1.0);
$engine->evaluateTransition($world); // mundane -> martial
$engine->evaluateTransition($world); // martial -> enhanced
$engine->evaluateTransition($world); // enhanced -> low_immortal
$world->refresh();

echo "Final Stage: " . $world->config['current_stage'] . "\n";
$canActivateDivineFinal = this_checkActivation($coupler, $world, $instanceDivine);
echo "Divine Kingship can NOW activate? " . ($canActivateDivineFinal ? 'YES' : 'NO') . "\n";

echo "--- VERIFICATION COMPLETE ---\n";

/**
 * Helper to check activation logic since it's private/internal
 */
function this_checkActivation($coupler, $world, $instance) {
    // We'll use a reflection or just rely on the fact that checkArchetypeActivation returns IDs
    $world->tick = 5; // Fake tick
    $world->save();
    
    // We need to ensure affinities allow it too
    // For this test, assume affinity is met
    $activatedIds = $coupler->checkArchetypeActivation($world);
    return in_array($instance->id, $activatedIds);
}
