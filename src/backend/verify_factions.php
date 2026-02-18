<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
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
$repo = app(CosmologyRepository::class);

echo "--- Testing COSMIC FACTIONS & ALLIANCES ---\n";

// 1. Seed Factions
echo "Seeding default factions...\n";
$factionService->ensureCommonFactionsExist();
$count = CosmicFaction::count();
echo "Factions seeded: {$count} (Expected 4)\n";

// 2. Test Suggestion
echo "Testing Suggestion Logic...\n";
$highOrderState = WorldStateVector::create(0.9, 0.1, 0.5, 0.5, 0.5, 0.5);
$u = new Universe($highOrderState, [], 'factory-test-1');
$suggestion = $factionService->getSuggestedFaction($u);
echo "Suggested for Order 0.9: {$suggestion} (Expected THE_CRYSTAL_ORDER)\n";

// 3. Test Join
echo "Testing Faction Join...\n";
$repo->save($u);
$factionService->joinFaction($u, 'The Crystal Order');
$model = UniverseModel::find('factory-test-1');
echo "Joined ID in DB: " . ($model->cosmic_faction_id ? 'YES' : 'NO') . "\n";

// 4. Test Buff Apply
echo "Testing Buff Application...\n";
$u = $repo->find('factory-test-1');
$uWithBuff = $factionService->applyFactionBuffs($u);

$oldOrder = $u->getState()->getOrder();
$newOrder = $uWithBuff->getState()->getOrder();
echo "Order before buff: {$oldOrder}\n";
echo "Order after buff: {$newOrder} (Expected > {$oldOrder})\n";

if ($newOrder > $oldOrder && $count === 4 && $suggestion === 'THE_CRYSTAL_ORDER') {
    echo "\nPHASE 19 VERIFICATION SUCCESS.\n";
} else {
    echo "\nPHASE 19 VERIFICATION COMPLETED WITH DISCREPANCIES.\n";
}
