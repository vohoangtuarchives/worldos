<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$timelineId = 'simulation_test';
$eventStore = new \App\StoryEngine\Persistence\EventStore();
$replayEngine = new \App\StoryEngine\Persistence\ReplayEngine($eventStore);

echo "Replaying timeline: $timelineId\n";
$start = microtime(true);

$worldState = $replayEngine->replay($timelineId);

$end = microtime(true);
echo "Replay Complete in " . round($end - $start, 4) . "s\n";

echo "Final World State:\n";
foreach ($worldState->factions as $f) {
    $success = array_sum($f->memory->successCounter);
    $fail = array_sum($f->memory->failureCounter);
    echo "Faction {$f->id}: Success=$success, Fail=$fail\n";
}
