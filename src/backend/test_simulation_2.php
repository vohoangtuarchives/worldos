<?php

use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaRunner;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sagaId = '019c4b19-3047-73f8-95c7-ca6abaadc26e'; // Current stuck saga
$saga = Saga::find($sagaId);

if (!$saga) {
    echo "Saga not found!\n";
    exit;
}

echo "Simulating Saga: {$saga->name} (Index: {$saga->current_world_index}, Status: {$saga->status})\n";

$runner = app(SagaRunner::class);

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Force run
    $runner->runSync($saga);
    echo "Simulation completed successfully.\n";
} catch (\Throwable $e) {
    echo "Simulation Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// Check results
$saga->refresh();
echo "Post-Sim Status: Index: {$saga->current_world_index}, Status: {$saga->status}\n";

$world = $saga->getCurrentWorld();
if ($world) {
    $chronicleCount = DB::table('chronicles')->where('world_id', $world->world_id)->count();
    $tick = \App\Models\World::find($world->world_id)->tick ?? 'N/A';
    echo "World {$world->sequence} (ID: {$world->world_id}): Tick {$tick}, Chronicles {$chronicleCount}\n";
} else {
    echo "No current world found after simulation.\n";
}
