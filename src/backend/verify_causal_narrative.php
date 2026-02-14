<?php

use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Saga\CausalNarrativeAssembler;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING CAUSAL NARRATIVE ENGINE ---\n";

$assembler = app(CausalNarrativeAssembler::class);

// 1. Define distinct states
$stateGolden = [
    'cosmic' => new CosmicState(0.1, 0.8, 0.5, 0.05, 0.9, 'EQUILIBRIUM', 1000),
    'civ' => new CivilizationState(1.5, 0.8, 1.2, 0.9, 0.5, 1.0, 1000),
    'events' => [['type' => 'default', 'severity' => 2]]
];

$stateCollapse = [
    'cosmic' => new CosmicState(0.9, 0.2, 0.8, 0.95, 0.1, 'CHAOS', 1000),
    'civ' => new CivilizationState(0.2, 0.1, 0.1, 0.1, 0.0, 0.1, 1000),
    'events' => [['type' => 'collapse_warning', 'severity' => 10, 'description' => 'The end is nigh.']]
];

// 2. Generate Narratives
echo "\n[TEST 1] Golden Age Generation:\n";
$textGolden1 = $assembler->assemble($stateGolden['events'], 1, $stateGolden['cosmic'], $stateGolden['civ']);
echo substr($textGolden1, 0, 150) . "...\n";

echo "\n[TEST 2] Collapse Generation:\n";
$textCollapse = $assembler->assemble($stateCollapse['events'], 1, $stateCollapse['cosmic'], $stateCollapse['civ']);
echo substr($textCollapse, 0, 150) . "...\n";

// 3. Verify Determinism
echo "\n[TEST 3] Determinism Check (Re-running Golden Age):\n";
$textGolden2 = $assembler->assemble($stateGolden['events'], 1, $stateGolden['cosmic'], $stateGolden['civ']);

if ($textGolden1 === $textGolden2) {
    echo "✅ SUCCESS: Output is identical for same state.\n";
} else {
    echo "❌ FAILURE: Output differs!\n";
    echo "Run 1: " . substr($textGolden1, 0, 50) . "\n";
    echo "Run 2: " . substr($textGolden2, 0, 50) . "\n";
    exit(1);
}

// 4. Verify Archetype Difference
if ($textGolden1 !== $textCollapse) {
    echo "✅ SUCCESS: Different states produce different narratives.\n";
} else {
    echo "❌ FAILURE: Output is identical for different states!\n";
    exit(1);
}

// 5. Check Keywords
if (str_contains($textGolden1, 'thịnh vượng') || str_contains($textGolden1, 'huy hoàng') || str_contains($textGolden1, 'lấp lánh')) {
    echo "✅ SUCCESS: Golden Age keywords found.\n";
} else {
    echo "⚠️ WARNING: Golden Age keywords missing.\n";
}

if (str_contains($textCollapse, 'tàn lụi') || str_contains($textCollapse, 'nứt toạc') || str_contains($textCollapse, 'tuyệt vọng')) {
    echo "✅ SUCCESS: Collapse keywords found.\n";
} else {
    echo "⚠️ WARNING: Collapse keywords missing.\n";
}
