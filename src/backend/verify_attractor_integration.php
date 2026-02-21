<?php

use App\Domains\Cosmology\Services\BifurcationManager;
use App\Domains\Cosmology\Services\MorphingEngine;
use App\Domains\Cosmology\Repositories\AttractorEloquentRepository;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\EnvironmentState;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Attractor Integration Verification ===\n\n";

// 1. Test BifurcationManager with AttractorRepository
$repo = new AttractorEloquentRepository();
$morphEngine = new MorphingEngine();
$bifurcationManager = new BifurcationManager($repo, $morphEngine);

echo "1. Testing database-backed BifurcationManager...\n";

$worldSnapshot = new WorldSnapshot(
    cosmic: new CosmicState(
        entropy: 0.3,
        energy: 0.5,
        causality: 0.4,
        strain: 0.95, // High strain to trigger bifurcation
        stability: 0.7,
        currentAttractor: 'EQUILIBRIUM',
        year: 1000,
        currentIncarnationId: '10000000-0000-0000-0000-000000000001'
    ),
    environment: EnvironmentState::defaultObservation(1000),
    civilization: CivilizationState::defaultObservation(1000),
    year: 1000
);

$result = $bifurcationManager->evaluate($worldSnapshot);

if ($result['bifurcated']) {
    echo "   ✓ Bifurcation triggered!\n";
    echo "   ✓ Event Type: {$result['event']['type']}\n";
    echo "   ✓ From: {$result['event']['from']} to {$result['event']['to']}\n";
    
    if (isset($result['event']['incarnation_id'])) {
        echo "   ✓ New Incarnation ID: {$result['event']['incarnation_id']}\n";
    }
    
    $newCosmic = $result['snapshot']->cosmic;
    if ($newCosmic->currentIncarnationId) {
        echo "   ✓ Cosmic State has incarnation ID: {$newCosmic->currentIncarnationId}\n";
        
        // Verify incarnation exists in DB
        $inc = DB::table('attractor_incarnations')
            ->where('id', $newCosmic->currentIncarnationId)
            ->first();
        
        if ($inc) {
            echo "   ✓ Incarnation persisted to database\n";
            echo "   ✓ Parent Incarnation: {$inc->parent_incarnation_id}\n";
            echo "   ✓ Start Tick: {$inc->start_tick}\n";
        } else {
            echo "   ✗ Incarnation NOT found in database!\n";
        }
    }
} else {
    echo "   ℹ No bifurcation (strain not high enough or DB not seeded)\n";
}

// 2. Test Incarnation Tree
echo "\n2. Testing Incarnation Tree...\n";
$equilibrium = $repo->findByCode('EQUILIBRIUM');
if ($equilibrium) {
    $tree = $equilibrium->getIncarnationTree();
    echo "   ✓ EQUILIBRIUM has " . count($tree) . " incarnation(s)\n";
    
    foreach ($tree as $inc) {
        echo "   - Incarnation {$inc->id}: Tick {$inc->start_tick} → " . ($inc->endTick ?? 'active') . "\n";
    }
}

echo "\n=== Verification Complete ===\n";
