<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\LifecycleService;
use App\Domains\Cosmology\Repositories\CosmologyRepository;

require __DIR__ . '/vendor/autoload.php';

// Need to boot Laravel to resolving bindings
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force SQLite for testing
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);

// Run migrations for the memory DB
Illuminate\Support\Facades\Artisan::call('migrate');

$repo = app(\App\Domains\Cosmology\Repositories\CosmologyRepository::class);
$service = new LifecycleService($repo);

// 1. Test Heat Death
echo "--- Testing HEAT DEATH ---\n";
$vDeath = WorldStateVector::create(0.0, 0.99, 0.0, 0.0, 0.0, 0.0);
$uDeath = new Universe($vDeath, [], 'dying-univ');
$repo->save($uDeath); // Persist so archive can find it

$cause = $service->checkDeath($uDeath);
echo "Death Cause (Expected: HEAT_DEATH): " . $cause . "\n";

if ($cause === 'HEAT_DEATH') {
    $service->archive($uDeath, $cause);
    // Reload to check is_archived
    $model = \App\Models\UniverseModel::find('dying-univ');
    echo "Is Archived: " . ($model->is_archived ? 'YES' : 'NO') . "\n";
}

// 2. Test Time Crunch
echo "\n--- Testing TIME CRUNCH ---\n";
// Create old universe
$vOld = WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5);
$uOld = new Universe($vOld, [], 'old-univ', 1001); // Age 1001
$repo->save($uOld);

$causeOld = $service->checkDeath($uOld);
echo "Death Cause (Expected: TIME_CRUNCH): " . $causeOld . "\n";

// 3. Test Spawn (requires world_id)
echo "\n--- Testing SPAWN ---\n";
$worldId = \App\Models\World::orderBy('id')->value('id');
if ($worldId === null) {
    echo "No World in DB; create one to test spawnNew.\n";
} else {
    $newU = $service->spawnNew((int) $worldId);
    echo "Spawned Universe ID: " . $newU->getId() . "\n";
    echo "New Universe Age: " . $newU->getAge() . "\n";
}
