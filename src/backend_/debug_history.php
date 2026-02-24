<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\World;
use App\Models\WorldEvent;
use Illuminate\Support\Facades\Artisan;

// 1. Get an autonomous world
$world = World::where('autonomous', true)->first();

if (!$world) {
    echo "No autonomous world found.\n";
    exit(1);
}

echo "World: {$world->name} (ID: {$world->id})\n";
$initialCount = WorldEvent::where('world_id', $world->id)->count();
echo "Initial Event Count: {$initialCount}\n";

// 2. Force multiple ticks to ensure at least one event triggers (10% chance)
echo "Forcing 20 ticks...\n";
for ($i = 0; $i < 20; $i++) {
    // We call the service directly or via artisan to speed it up
    Artisan::call('autonomous:tick', ['--force' => true]);
    echo ".";
}
echo "\n";

// 3. Check results
$finalCount = WorldEvent::where('world_id', $world->id)->count();
echo "Final Event Count: {$finalCount}\n";

if ($finalCount > $initialCount) {
    echo "SUCCESS: " . ($finalCount - $initialCount) . " new events generated.\n";
    
    $newEvents = WorldEvent::where('world_id', $world->id)
        ->latest()
        ->take($finalCount - $initialCount)
        ->get();
        
    foreach ($newEvents as $event) {
        echo "- [Tick {$event->tick}] {$event->type}: {$event->payload['description']}\n";
    }
} else {
    echo "WARNING: No events generated (could be bad luck, try again).\n";
}
