<?php

use App\Models\World;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmic\Services\WorldEvolutionPipeline;
use App\Domains\Cosmic\Repositories\CosmicSnapshotEloquentRepository;
use Tuzy\Domain\Cosmology\Enums\SocialClassType;
use Tuzy\Domain\Cosmology\ValueObject\SocialClass;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create a dummy world for testing
$world = World::create([
    'name' => 'Social Verification World',
    'genre' => 'fantasy',
    'preset' => 'standard',
    'health_status' => 'STABLE',
    'status' => 'active',
    'tick' => 0,
    'current_epoch' => 0,
    'gene_vector' => ['a' => 1, 'b' => 2],
    'physics_profile' => ['entropy_drift' => 0.01],
]);

echo "World created: ID {$world->id}\n";

// 2. Setup initial state with high Intellectual power
$civ = CivilizationState::defaultObservation(0);
$classes = array_map(function($c) {
    if ($c->type === SocialClassType::INTELLECTUAL) {
        return new SocialClass($c->type, 0.8, 0.9, 0.05); // High Intellectual power
    }
    return $c;
}, $civ->socialClasses);

$initialCiv = new CivilizationState(
    collectiveKnowledge: 0.1,
    ritualCoherence: 0.5,
    technologicalLevel: 0.1,
    factionStability: 0.8,
    resonanceAccumulator: 0.0,
    resilience: 1.0,
    year: 0,
    socialClasses: $classes
);

$snapshot = new WorldSnapshot(
    cosmic: \App\Domains\Cosmic\ValueObjects\CosmicState::defaultObservation(0),
    environment: \App\Domains\Cosmic\ValueObjects\EnvironmentState::defaultObservation(0),
    civilization: $initialCiv,
    year: 0
);

// 3. Save initial snapshot
$repo = app(CosmicSnapshotEloquentRepository::class);
$repo->saveSnapshot($world->id, $snapshot);

// Verify persistence
$saved = $world->cosmicSnapshots()->where('year', 0)->first();
if ($saved && !empty($saved->social_classes)) {
    echo "SUCCESS: Social classes persisted to database.\n";
    foreach ($saved->social_classes as $sc) {
        if ($sc['type'] === 'INTELLECTUAL') {
            echo "   Intellectual Power: {$sc['power']}\n";
        }
    }
} else {
    echo "FAILURE: Social classes NOT persisted.\n";
    exit(1);
}

// 4. Run simulation step
$pipeline = app(WorldEvolutionPipeline::class);
echo "Stepping 100 years...\n";
$next = $pipeline->step($snapshot, 0.0, 100);

// 5. Verify Modifiers (Intellectual Power -> Knowledge Growth)
echo "Initial Knowledge: " . $initialCiv->collectiveKnowledge . "\n";
echo "Next Knowledge: " . $next->civilization->collectiveKnowledge . "\n";

if ($next->civilization->collectiveKnowledge > $initialCiv->collectiveKnowledge) {
    echo "SUCCESS: Knowledge grew with social modifiers!\n";
} else {
    echo "FAILURE: Knowledge did not grow.\n";
}

// Cleanup
$world->cosmicSnapshots()->delete();
$world->delete();
echo "Cleanup complete.\n";
