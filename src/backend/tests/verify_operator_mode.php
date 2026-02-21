<?php

use App\Models\World;
use Tuzy\Domain\World\Enums\WorldHealthStatus;
use App\Domains\WorldManagement\Services\AlertService;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- TEST: Operator Mode (ADR-0008) ---\n";

// 1. Create World
echo "[1] Creating Test World...\n";
$world = World::create([
    'name' => 'Operator Test World',
    'status' => 'ACTIVE',
    'health_status' => WorldHealthStatus::STABLE,
    'law_profile' => \Tuzy\Domain\World\ValueObject\WorldLawProfile::default(),
]);
echo "    World Created: ID {$world->id}\n";

// 2. Mock Metrics (Critical State)
echo "[2] Testing Alert Service Thresholds...\n";
$metrics = [
    'faction_stats' => [ // High Stress
        ['stress' => 95], // Avg > 90 for CRITICAL ECONOMY_DEAD
        ['stress' => 95],
    ],
    'fork_rate' => 0,
];

// Mock Rejected Generations
// We need to create some rejected generations first
\Illuminate\Support\Facades\DB::table('ai_generations')->insert([
    [
        'world_id' => $world->id, 
        'user_prompt' => 'test', 
        'system_prompt' => 'sys', 
        'prompt_hash' => 'hash1',
        'response_content' => 'test', 
        'status' => 'REJECTED', 
        'created_at' => now()
    ],
    [
        'world_id' => $world->id, 
        'user_prompt' => 'test', 
        'system_prompt' => 'sys', 
        'prompt_hash' => 'hash2',
        'response_content' => 'test', 
        'status' => 'REJECTED', 
        'created_at' => now()
    ],
    [
        'world_id' => $world->id, 
        'user_prompt' => 'test', 
        'system_prompt' => 'sys', 
        'prompt_hash' => 'hash3',
        'response_content' => 'test', 
        'status' => 'REJECTED', 
        'created_at' => now()
    ],
    [
        'world_id' => $world->id, 
        'user_prompt' => 'test', 
        'system_prompt' => 'sys', 
        'prompt_hash' => 'hash4',
        'response_content' => 'test', 
        'status' => 'ACCEPTED', 
        'created_at' => now()
    ],
]); 
// 3/4 = 75% Reject Rate

$service = new AlertService();
$newStatus = $service->checkHealth($world, $metrics);

echo "    Calculated Status: {$newStatus->value}\n";

if ($newStatus === WorldHealthStatus::CRITICAL) {
    echo "    [PASS] Correctly identified CRITICAL state.\n";
} else {
    echo "    [FAIL] Expected CRITICAL, got {$newStatus->value}.\n";
}

// 3. Check Alerts
$alerts = \App\Models\WorldAlert::where('world_id', $world->id)->get();
echo "    Alerts Generated: " . $alerts->count() . "\n";
foreach ($alerts as $a) {
    echo "      - [{$a->severity}] {$a->type}: {$a->message}\n";
}

if ($alerts->count() >= 2) {
    echo "    [PASS] Generated required alerts.\n";
} else {
    echo "    [FAIL] Missing alerts.\n";
}

// 4. Test Kill Switch (Simulation)
echo "[3] Testing Simulator Kill Switch Integration...\n";
// Create Simulator and Run
// This will trigger MetricsPhase -> AlertService -> Update World
// We expect the world status to persist.

$simulator = new \App\StoryEngine\Simulator();
$simulator->world = new \App\StoryEngine\WorldState();
$simulator->world->id = $world->id; // Inject ID
$simulator->timelineId = $world->name;

// Initialize Factions for Phase V/W (Simulator expects them)
$simulator->world->factions = [
    new \App\StoryEngine\FactionState('sect_1', 'Test Sect', 'Sect'),
    new \App\StoryEngine\FactionState('clan_1', 'Test Clan', 'Clan'),
];

// Run 1 tick - Should be fine (CRITICAL but not HALTED)
echo "    Running Tick 1 (CRITICAL state)...\n";
$metrics = $simulator->run(1);
echo "    Tick 1 Completed. Metrics count: " . count($metrics) . "\n";

// Now Manually HALT the world
echo "    Activating Kill Switch (Manual Halt)...\n";
$world->update(['health_status' => WorldHealthStatus::HALTED]);

// Run 1 tick - Should Stop
echo "    Running Tick 2 (HALTED state)...\n";
$metricsHalt = $simulator->run(1);

if (isset($metricsHalt[0]['status']) && $metricsHalt[0]['status'] === 'HALTED') {
    echo "    [PASS] Simulator correctly stopped execution.\n";
} else {
    echo "    [FAIL] Simulator continued running!\n";
    print_r($metricsHalt);
}

// 5. Test Fork Explosion Alert (DEGRADED RULE: Fork Rate >= 3)
echo "[4] Testing Fork Explosion Alert...\n";
// Create 4 forks (Limit is 3/day for DEGRADED)
for ($i = 0; $i < 4; $i++) {
    World::create([
        'name' => "Fork $i",
        'parent_id' => $world->id,
        'status' => 'ACTIVE',
        'health_status' => WorldHealthStatus::STABLE,
        'law_profile' => \Tuzy\Domain\World\ValueObject\WorldLawProfile::default(),
        'created_at' => now(),
    ]);
}

// We need to inject metrics manually for test as Simulator logic for metrics is inside Sim
$metrics['fork_rate'] = 4; // Mock the metric derived from DB

$service->checkHealth($world, $metrics); 
$forkAlert = \App\Models\WorldAlert::where('world_id', $world->id)
    ->where('type', 'FORK_EXPLOSION')
    ->first();

if ($forkAlert) {
    echo "    [PASS] FORK_EXPLOSION Alert generated.\n";
} else {
    echo "    [FAIL] Missing Fork Explosion Alert.\n";
}

// 6. Test Safe Mode Status
echo "[5] Testing Safe Mode Status...\n";
$world->update(['status' => 'SAFE_MODE']);
$fresh = World::find($world->id);
if ($fresh->status === 'SAFE_MODE') {
    echo "    [PASS] World Status set to SAFE_MODE.\n";
} else {
    echo "    [FAIL] World Status update failed.\n";
}

// Cleanup
$world->delete();
World::where('parent_id', $world->id)->delete(); // Delete forks
\App\Models\WorldAlert::where('world_id', $world->id)->delete();
\Illuminate\Support\Facades\DB::table('ai_generations')->where('world_id', $world->id)->delete();
echo "--- TEST COMPLETE ---\n";
