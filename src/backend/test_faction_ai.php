<?php

use App\Domains\Saga\Saga;
use App\Models\World;
use App\Models\Faction;
use App\Domains\Saga\SagaWorld;
use App\Domains\Saga\SagaRunner;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Setup Test Data
$saga = Saga::first() ?? Saga::create(['name' => 'AI Test Saga', 'world_count' => 1]);
$world = World::create([
    'name' => 'AI Test World',
    'status' => 'active',
    'tick' => 0,
    'genre' => 'historical'
]);

// Create 3 Factions
for ($i = 1; $i <= 3; $i++) {
    Faction::create([
        'world_id' => $world->id,
        'name' => "Faction $i",
        'type' => 'empire',
        'attributes' => ['military' => rand(50, 100)]
    ]);
}

echo "Created world {$world->id} with 3 factions.\n";

// 2. Wrap World in SagaWorld
$sagaWorld = SagaWorld::create([
    'saga_id' => $saga->id,
    'world_id' => $world->id,
    'sequence' => 0,
    'status' => SagaWorld::STATUS_PENDING
]);

// 3. Run Simulation (manually calling simulateWorld for 5 ticks)
$runner = app(SagaRunner::class);

echo "Starting 5-tick simulation...\n";

// Use reflection to call private simulateWorld if needed, or just run a small portion
$reflection = new ReflectionClass($runner);
$method = $reflection->getMethod('simulateWorld');
$method->setAccessible(true);

$method->invoke($runner, $sagaWorld);

echo "Simulation complete.\n";

// 4. Verify Logs
echo "\n--- Faction AI Logs ---\n";
$logs = \App\Models\FactionHistoryLog::whereIn('faction_id', $world->factions->pluck('id'))->get();
foreach ($logs as $log) {
    echo "Turn {$log->turn} | Faction {$log->faction_id} chose: {$log->intent_type} (Outcome: {$log->outcome_score})\n";
}

echo "\n--- Faction Personality Drift ---\n";
foreach ($world->factions as $f) {
    $p = $f->getPersonality();
    echo "Faction {$f->name} Personality: Aggression: {$p->aggression}, Faith: {$p->faith}, Fear: {$p->fear}\n";
}

echo "\nDone.\n";
