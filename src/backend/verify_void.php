<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\HarbingerService;
use App\Domains\Cosmology\Services\GlobalDefenseService;
use App\Models\MultiverseMeta;
use App\Models\UniverseModel;
use App\Domains\Cosmology\Repositories\CosmologyRepository;

require __DIR__ . '/vendor/autoload.php';

// Boot App
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force SQLite for testing
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);
Illuminate\Support\Facades\Artisan::call('migrate');

$harbingerService = app(HarbingerService::class);
$defenseService = app(GlobalDefenseService::class);
$repo = app(CosmologyRepository::class);

echo "--- Testing PHASE 22: VOID INCURSION & DEFENSE ---\n";

// 1. Initial State
$meta = MultiverseMeta::first();
echo "Initial Leak: {$meta->entropy_leak}\n";

// 2. Create High Entropy Universes
echo "Creating 5 high-entropy universes to trigger leak...\n";
for ($i=0; $i<5; $i++) {
    $u = new Universe(WorldStateVector::create(0.1, 0.9, 0.5, 0.5, 0.5, 0.5), [], 'u'.$i, 0, ['x' => rand(-1000, 1000), 'y' => rand(-1000, 1000), 'z' => rand(-1000, 1000)]);
    $repo->save($u);
}

// 3. Process Global Threat
echo "Processing Global Threat (Cycle 1)...\n";
$activeModels = UniverseModel::all();
$activeUniverses = array_map(fn($m) => $repo->find($m->id), $activeModels->all());
$meta = $harbingerService->processGlobalThreat($activeUniverses);
echo "Leak after cycle 1: {$meta->entropy_leak}\n";

// 4. Force High Leak to trigger Void Spawn
echo "Forcing high leak to spawn Void Zone...\n";
$meta->entropy_leak = 1.2;
$meta->save();
$meta = $harbingerService->processGlobalThreat($activeUniverses);
echo "Leak after spawn: {$meta->entropy_leak}\n";
echo "Void Zones count: " . count($meta->void_zones) . "\n";

// 5. Test Void Consumption
if (!empty($meta->void_zones)) {
    $zone = $meta->void_zones[0];
    echo "Placing a universe inside Void Zone...\n";
    $uInside = new Universe(WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 1.0), [], 'u_inside', 0, $zone['coords']);
    $repo->save($uInside);
    
    $uAfter = $harbingerService->applyVoidIncursion($uInside);
    $oldRes = $uInside->getState()->getResourceStock();
    $newRes = $uAfter->getState()->getResourceStock();
    echo "Resource before void: {$oldRes}\n";
    echo "Resource after void: {$newRes} (Expected < {$oldRes})\n";
}

// 6. Test Defense Contribution
echo "Testing Multiversal Defense...\n";
$oldShield = $meta->shield_level;
$defenseService->contribute('u0', 0.5);
$meta->refresh();
echo "Shield level after contribution: {$meta->shield_level} (Expected > {$oldShield})\n";

if ($meta->entropy_leak > 0 && count($meta->void_zones) > 0 && $newRes < $oldRes && $meta->shield_level > $oldShield) {
    echo "\nPHASE 22 VERIFICATION SUCCESS.\n";
} else {
    echo "\nPHASE 22 VERIFICATION COMPLETED WITH DISCREPANCIES.\n";
}
