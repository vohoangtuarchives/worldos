<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\ConvergenceService;
use App\Domains\Cosmology\Services\BifurcationService;
use App\Domains\Cosmology\Services\LifecycleService;
use App\Domains\Cosmology\Services\SagaGeneratorService;
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
$saga = app(SagaGeneratorService::class);
$lifecycle = new LifecycleService($repo, $saga);
$convergence = new ConvergenceService($repo, $lifecycle);
$bifurcation = new BifurcationService($repo);

echo "--- Testing MULTIVERSE EVOLUTION ---\n";

// 1. Test Convergence
echo "Testing CONVERGENCE...\n";
$u1 = new Universe(WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5), [], 'u1', 100, ['x' => 0, 'y' => 0, 'z' => 0]);
$u2 = new Universe(WorldStateVector::create(0.6, 0.4, 0.6, 0.6, 0.6, 0.6), [], 'u2', 120, ['x' => 10, 'y' => 10, 'z' => 10]);
$repo->save($u1);
$repo->save($u2);

$merged = $convergence->merge($u1, $u2);
echo "New Universe ID: " . $merged->getId() . "\n";
echo "New Cohesion (with 10% bonus): " . $merged->getState()->getCohesion() . "\n";

$model1 = UniverseModel::find('u1');
echo "Parent 1 Archived: " . ($model1->is_archived ? 'YES' : 'NO') . " Cause: " . $model1->death_cause . "\n";
echo "Saga Preview: " . $model1->saga . "\n";

$newModel = UniverseModel::find($merged->getId());
echo "Child Ancestors: " . implode(', ', $newModel->parameters['ancestors']) . "\n";

// 2. Test Bifurcation
echo "\nTesting BIFURCATION...\n";
$root = new Universe(WorldStateVector::create(0.8, 0.2, 0.8, 0.8, 0.9, 0.8), [], 'root', 300, ['x' => 500, 'y' => 500, 'z' => 500]);
$repo->save($root);

$branches = $bifurcation->split($root);
echo "Bifurcation created " . count($branches) . " branches.\n";

$lifecycle->archive($root, 'BIFURCATION');
$modelRoot = UniverseModel::find('root');
echo "Root Saga Preview: " . $modelRoot->saga . "\n";

$branch1Model = UniverseModel::find($branches[0]->getId());
echo "Branch 1 Saga: " . $saga->generateSaga($branches[0], 'NONE') . "\n";

if ($modelRoot->is_archived && count($branches) === 2) {
    echo "EVOLUTION SUCCESS.\n";
} else {
    echo "EVOLUTION FAILURE.\n";
}
