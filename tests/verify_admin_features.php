<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\World;
use App\Domains\World\Services\WorldForkService;
use Illuminate\Support\Facades\DB;

echo "--- TEST: Admin Features (ADR-0007) ---\n";

// 1. Setup Base World
$baseWorld = World::create(['name' => 'Admin Base World']);
echo "Base World: {$baseWorld->name} (ID: {$baseWorld->id}, Status: {$baseWorld->status})\n";

// 2. Test Locking
echo "Testing Lock...\n";
$baseWorld->update(['status' => 'LOCKED']);
$baseWorld->refresh();
if ($baseWorld->status === 'LOCKED') {
    echo "[PASS] World Locked.\n";
} else {
    echo "[FAIL] World Lock Failed.\n";
}

// 3. Test Unlocking
echo "Testing Unlock...\n";
$baseWorld->update(['status' => 'ACTIVE']);
$baseWorld->refresh();
if ($baseWorld->status === 'ACTIVE') {
    echo "[PASS] World Unlocked.\n";
} else {
    echo "[FAIL] World Unlock Failed.\n";
}

// 4. Test Forking with Parent Tracking
echo "Testing Fork Hierarchy...\n";
$forkService = new WorldForkService();
$childWorld = $forkService->fork($baseWorld, 0, 'Admin Forked World');

echo "Child World: {$childWorld->name} (ID: {$childWorld->id})\n";
if ($childWorld->parent_id === $baseWorld->id) {
    echo "[PASS] Parent ID correct ({$childWorld->parent_id}).\n";
} else {
    echo "[FAIL] Parent ID mismatch (Expected {$baseWorld->id}, got {$childWorld->parent_id}).\n";
}

// Cleanup
// $childWorld->delete();
// $baseWorld->delete();
