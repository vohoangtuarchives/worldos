<?php

use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaRunner;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sagaId = '019c4b19-3047-73f8-95c7-ca6abaadc26e';
$saga = Saga::find($sagaId);

if (!$saga) {
    echo "Saga not found!\n";
    exit;
}

echo "Resetting Saga: {$saga->name} (Current Index: {$saga->current_world_index})\n";

$saga->current_world_index = 0;
$saga->status = Saga::STATUS_RUNNING;
$saga->save();

// Also reset Sequence 0 world if exists
$sagaWorld = \App\Domains\Saga\SagaWorld::where('saga_id', $saga->id)
    ->where('sequence', 0)
    ->first();

if ($sagaWorld) {
    echo "Resetting World 0 (ID: {$sagaWorld->world_id})...\n";
    $sagaWorld->status = 'pending';
    $sagaWorld->save();
    
    $world = \App\Models\World::find($sagaWorld->world_id);
    if ($world) {
        $world->tick = 0;
        $world->save();
        DB::table('chronicles')->where('world_id', $world->id)->delete();
    }
} else {
    echo "World 0 not found. It will be created.\n";
}

echo "Saga Reset Complete.\n";

// Optional: Run simulation immediately to verify
$runner = app(SagaRunner::class);
echo "Running Test Simulation...\n";
try {
   $runner->runSync($saga);
   echo "Test Run Complete.\n";
} catch (\Throwable $e) {
   echo "Test Run Failed: " . $e->getMessage() . "\n";
}
