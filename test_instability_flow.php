<?php

use App\Models\World;
use App\Domains\Saga\SagaRunner;
use App\Domains\CognitiveKernel\ArchetypePool;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$world = World::create([
    'name' => 'Instability Test World',
    'status' => 'active',
    'tick' => 10,
    'genre' => 'historical',
]);

$pool = new ArchetypePool();
$pool->initializeForWorld($world);

echo "Testing Global Instability Score...\n";

$runner = app(SagaRunner::class);

// 1. Initial State (Neutral)
echo "\n--- Neutral State ---\n";
$score = $runner->calculateGlobalInstability($world);
echo "Instability Score: " . number_format($score, 4) . " (Expected: Low)\n";

// 2. Extreme Social Tension
echo "\n--- Extreme Social Tension ---\n";
$pool->updateWeight($world, 'hierarchy', 0.95);
$score = $runner->calculateGlobalInstability($world);
$reason = $runner->getCollapseReason($world);
echo "Instability Score: " . number_format($score, 4) . "\n";
echo "Primary Collapse Reason: {$reason}\n";

// Reset
$pool->updateWeight($world, 'hierarchy', 0.5);

// 3. Extreme Power Pressure
echo "\n--- Extreme Power Pressure ---\n";
$pool->updateWeight($world, 'domination', 0.95);
$score = $runner->calculateGlobalInstability($world);
$reason = $runner->getCollapseReason($world);
echo "Instability Score: " . number_format($score, 4) . "\n";
echo "Primary Collapse Reason: {$reason}\n";

// 4. Massive Trauma (Scar)
echo "\n--- Adding Massive Trauma (Scar) ---\n";
// Create a dummy timeline if needed or just use null if allowed
$event = \App\Models\WorldEvent::create([
    'world_id' => $world->id,
    'type' => 'Cataclysm',
    'tick' => 10,
    'payload' => json_encode(['description' => 'The Great Breaking', 'severity' => 10])
]);

$scar = \App\Models\Scar::create([
    'world_id' => $world->id,
    'origin_event_id' => $event->id,
    'wound_type' => 'Great Cataclysm',
    'pain_score' => 5.0, // Massive pain
    'created_tick' => 10,
    'decay_rate' => 0.01,
    'belief_shift_vector' => []
]);
$score = $runner->calculateGlobalInstability($world);
echo "Instability Score after Scar: " . number_format($score, 4) . "\n";
echo "Collapse Triggered: " . ($runner->checkCollapse($world) ? 'YES' : 'NO') . "\n";

// 4. Verification of Collapse Trigger
$isCollapsed = $runner->checkCollapse($world);
echo "\nCollapse Triggered: " . ($isCollapsed ? 'YES' : 'NO') . "\n";

$world->delete();
echo "\nTest Completed.\n";
