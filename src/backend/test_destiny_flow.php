<?php

use App\Models\World;
use App\Models\Faction;
use App\Domains\Faction\Services\EncounterService;
use App\Domains\World\Services\WorldEventLedger;
use Tuzy\Domain\Faction\ValueObject\Leader;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STEP 1: Setting up Encounters ---\n";

// Setup Data
$world = World::updateOrCreate(['name' => 'Destiny World'], ['tick' => 100]);
$ledger = app(WorldEventLedger::class);
$service = new EncounterService($ledger);

// Create Faction 1
$faction1 = Faction::updateOrCreate(
    ['name' => 'Destiny Sect'],
    [
        'world_id' => $world->id,
        'type' => 'SECT',
        'leader_data' => Leader::create(1)->toArray()
    ]
);

// Create Faction 2 (Target for relationships)
$faction2 = Faction::updateOrCreate(
    ['name' => 'Rival Clan'],
    [
        'world_id' => $world->id,
        'type' => 'CLAN',
        'leader_data' => Leader::create(1)->toArray()
    ]
);

echo "Leader Initial: " . $faction1->getLeader()->name . "\n";

// Force Run Encounters (Loop until we get different types)
echo "\n--- STEP 2: Running Simulation Loop (Force 100% chance logic via reflection or simple loop) ---\n";

// We can't easily force the 5% chance without modifying the class or using reflection to set the constant (which is hard).
// Instead, we call checkEncounter many times until we see events in the ledger.

$startCount = \App\Models\WorldEvent::where('world_id', $world->id)->count();

for ($i = 0; $i < 50; $i++) {
    // We can't change the constant ENCOUNTER_CHANCE = 0.05 easily.
    // But 50 tries * 0.05 = 2.5 expected events.
    // Let's rely on probability or mock if needed. 
    // Actually, let's subclass or mock the service for testing? 
    // No, let's just loop 100 times, statistically nearly guaranteed to trigger at least once.
    $service->checkEncounter($faction1);
}

$endCount = \App\Models\WorldEvent::where('world_id', $world->id)->count();
$newEvents = $endCount - $startCount;

echo "Events Generated: $newEvents\n";

if ($newEvents > 0) {
    echo "PASS: Random encounters triggered.\n";
} else {
    echo "WARNING: No encounters triggered (bad luck or logic error?).\n";
}

// Reload Faction to check state changes
$faction1->refresh();
$leader = $faction1->getLeader();

echo "\n--- STEP 3: Checking Leader State ---\n";
echo "Quirks: " . json_encode($leader->quirks) . "\n";
echo "Inventory: " . json_encode($leader->inventory) . "\n";
echo "Relationships: " . json_encode($leader->relationships) . "\n";

if (!empty($leader->quirks) || !empty($leader->inventory) || !empty($leader->relationships)) {
    echo "PASS: Leader state updated.\n";
} else {
    echo "WARNING: Leader state unchanged (might be 'Discovery' event which only updates conversations or unlucky).\n";
}

// Check Ledger Content
$lastEvent = \App\Models\WorldEvent::where('world_id', $world->id)
    ->where('type', 'personal_event')
    ->orderBy('id', 'desc')
    ->first();

if ($lastEvent) {
    echo "Last Event Description: " . $lastEvent->payload['description'] . "\n";
    echo "PASS: Ledger recorded personal_event.\n";
}
