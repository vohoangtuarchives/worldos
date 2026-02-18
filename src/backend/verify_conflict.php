<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\ConflictService;
use App\Domains\Cosmology\Services\FactionService;
use App\Models\CosmicFaction;
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

$factionService = app(FactionService::class);
$conflictService = app(ConflictService::class);
$repo = app(CosmologyRepository::class);

echo "--- Testing PHASE 21: INTER-FACTION CONFLICT & EDICTS ---\n";

// 1. Setup Factions
$factionService->ensureCommonFactionsExist();
$crystalFaction = CosmicFaction::where('ideology', 'HARMONY')->first();
$swarmFaction = CosmicFaction::where('ideology', 'CHAOS')->first();

// 2. Setup Universes in proximity
echo "Creating two rival universes in close proximity...\n";
$u1 = new Universe(WorldStateVector::create(0.8, 0.1, 0.8, 0.8, 0.5, 0.5), [], 'u1', 0, ['x' => 100, 'y' => 100, 'z' => 100], $crystalFaction->id);
$u2 = new Universe(WorldStateVector::create(0.1, 0.8, 0.5, 0.5, 0.5, 0.5), [], 'u2', 0, ['x' => 150, 'y' => 150, 'z' => 150], $swarmFaction->id);

$repo->save($u1);
$repo->save($u2);

// 3. Test Friction
echo "Applying Friction...\n";
$activeModels = UniverseModel::all();
$u1AfterFriction = $conflictService->applyFriction($u1, $activeModels->all());

$oldCohesion = $u1->getState()->getCohesion();
$newCohesion = $u1AfterFriction->getState()->getCohesion();

echo "U1 Cohesion before friction: {$oldCohesion}\n";
echo "U1 Cohesion after friction: {$newCohesion}\n";

// 4. Test Edicts
echo "Testing Faction Edicts...\n";
$crystalFaction->stats = array_merge($crystalFaction->stats, ['active_edict' => 'TOTALITARIAN_STABILITY']);
$crystalFaction->save();

$u1AfterEdict = $factionService->applyEdicts($u1);
$oldOrder = $u1->getState()->getOrder();
$newOrder = $u1AfterEdict->getState()->getOrder();
$newInequality = $u1AfterEdict->getState()->getInequality();

echo "U1 Order before edict: {$oldOrder}\n";
echo "U1 Order after edict: {$newOrder} (Expected increase)\n";
echo "U1 Inequality after edict: {$newInequality} (Expected increase)\n";

if ($newCohesion < $oldCohesion && $newOrder > $oldOrder && $newInequality > 0.0) {
    echo "\nPHASE 21 VERIFICATION SUCCESS.\n";
} else {
    echo "\nPHASE 21 VERIFICATION COMPLETED WITH DISCREPANCIES.\n";
}
