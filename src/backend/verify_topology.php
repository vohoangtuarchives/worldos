<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\CouplingService;
use App\Domains\Cosmology\Services\CrisisService;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Models\UniverseModel;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

// Boot App
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force SQLite for testing
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);
Illuminate\Support\Facades\Artisan::call('migrate');

$repo = app(CosmologyRepository::class);
$coupling = new CouplingService();
$crisis = new CrisisService($repo);

echo "--- Testing SPATIAL TOPOLOGY ---\n";

// 1. Create two universes near each other
$u1 = new Universe(WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5), [], 'u1', 0, ['x' => 0, 'y' => 0, 'z' => 0]);
$u2 = new Universe(WorldStateVector::create(0.5, 0.9, 0.5, 0.5, 0.5, 0.5), [], 'u2', 0, ['x' => 10, 'y' => 0, 'z' => 0]); // Spatial distance 10

$dist = $coupling->calculateSpatialDistance($u1, $u2);
echo "Spatial Distance: $dist (Expected: 10)\n";

// 2. Test Resonance
echo "Testing Spatial Resonance (U2 -> U1)...\n";
$newState = $coupling->interact($u1, [$u2], 0.1);

if ($newState) {
    echo "Resonance SUCCESS: Entropy bleed detected.\n";
    echo "Old Entropy: 0.5, New Entropy: " . $newState->getEntropy() . "\n";
} else {
    echo "Resonance FAILURE: No interaction.\n";
}

// 3. Test Regional Crisis
echo "\nTesting REGIONAL CRISIS (Void Leak at origin)...\n";
$repo->save($u1);
$repo->save($u2);

$affected = $crisis->triggerRegionalCrisis(['x' => 0, 'y' => 0, 'z' => 0], 50, 'VOID_LEAK');
echo "Affected Universes: " . count($affected) . "\n";

$updatedU1 = $repo->find('u1');
echo "U1 Trauma: " . $updatedU1->getState()->getTrauma() . " (Expected > 0)\n";

if (count($affected) >= 1 && $updatedU1->getState()->getTrauma() > 0) {
    echo "CRISIS SUCCESS: Regional anomaly applied.\n";
} else {
    echo "CRISIS FAILURE.\n";
}
