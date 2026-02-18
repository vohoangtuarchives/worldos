<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\LifecycleService;
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

// Run migrations for the memory DB
Illuminate\Support\Facades\Artisan::call('migrate');

$repo = app(\App\Domains\Cosmology\Repositories\CosmologyRepository::class);
$sagaService = app(\App\Domains\Cosmology\Services\SagaGeneratorService::class);
$service = new LifecycleService($repo, $sagaService);

echo "--- Testing SAGA GENERATION ---\n";

// Create a dying universe
$id = (string) Str::uuid();
$vec = WorldStateVector::create(0.0, 0.99, 0.0, 0.0, 0.0, 0.0);
$u = new Universe($vec, [], $id, 500);
$repo->save($u);

// Archive it
echo "Archiving Universe $id...\n";
$service->archive($u, 'HEAT_DEATH');

// Verify DB
$model = UniverseModel::find($id);
echo "Is Archived: " . ($model->is_archived ? 'YES' : 'NO') . "\n";
echo "Death Cause: " . $model->death_cause . "\n";
echo "Saga Length: " . strlen($model->saga) . "\n";
echo "Saga Content: " . $model->saga . "\n";

if (strlen($model->saga) > 10) {
    echo "SUCCESS: Saga generated and saved.\n";
} else {
    echo "FAILURE: Saga missing or too short.\n";
}
