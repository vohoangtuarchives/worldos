<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\AnomalyService;
use App\Domains\Cosmology\Services\SagaGeneratorService;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Models\UniverseModel;

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
$saga = app(SagaGeneratorService::class);
$anomalyService = new AnomalyService();

echo "--- Testing COSMIC ANOMALIES & MILESTONES ---\n";

$u = new Universe(WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5), [], 'test-univ', 100, ['x' => 0, 'y' => 0, 'z' => 0]);
$repo->save($u);

// 1. Manually Trigger Golden Age
echo "Triggering GOLDEN_AGE...\n";
$u = $anomalyService->applyAnomaly($u, AnomalyService::TYPE_GOLDEN_AGE);
echo "New Innovation: " . $u->getState()->getInnovation() . " (Expected > 0.5)\n";

// 2. Manually Trigger Void Storm
echo "Triggering VOID_STORM...\n";
$u = $anomalyService->applyAnomaly($u, AnomalyService::TYPE_VOID_STORM);
echo "New Entropy: " . $u->getState()->getEntropy() . " (Expected high)\n";

// 3. Check Milestones
$params = $u->getParameters();
echo "Milestones count: " . count($params['milestones'] ?? []) . "\n";
foreach ($params['milestones'] as $m) {
    echo "- Cycle {$m['age']}: {$m['event']}\n";
}

// 4. Generate Saga
echo "\nGenerating Final Saga...\n";
$sagaText = $saga->generateSaga($u, 'HEAT_DEATH');
echo "Saga Snippet:\n" . substr($sagaText, 0, 500) . "...\n";

if (count($params['milestones']) === 2 && strpos($sagaText, 'GOLDEN_AGE') !== false) {
    echo "\nPHASE 18 VERIFICATION SUCCESS.\n";
} else {
    echo "\nPHASE 18 VERIFICATION FAILURE.\n";
}
