<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$events = Illuminate\Support\Facades\DB::table('world_events')
    ->where('timeline_id', 'simulation_test')
    ->orderBy('chapter', 'desc')
    ->limit(5)
    ->get();

if ($events->count() > 0) {
    echo "Found " . $events->count() . " events for 'simulation_test'.\n";
    foreach ($events as $e) {
        echo "[Chapter {$e->chapter}] Type: {$e->type}\n";
        echo "  Payload: " . substr($e->payload, 0, 100) . "...\n";
        echo "  World ID: " . ($e->world_id ?? 'NULL') . "\n";
    }
} else {
    echo "No events found for 'simulation_test'.\n";
    
    // Check ANY events
    $all = Illuminate\Support\Facades\DB::table('world_events')->count();
    echo "Total events in table: $all\n";
}
