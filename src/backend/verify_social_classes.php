<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\EnvironmentState;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use App\Domains\Cosmology\Services\WorldEvolutionPipeline;
use App\Domains\Cosmology\Services\CosmicEvolutionService;
use App\Domains\Cosmology\Services\BifurcationManager;
use App\Domains\Cosmology\Services\SocialDynamicsService;
use Tuzy\Domain\Cosmology\Enums\SocialClassType;

// 1. Mock the services
$cosmicService = Mockery::mock(CosmicEvolutionService::class);
$bifurcationManager = Mockery::mock(BifurcationManager::class);
$socialDynamicsService = new SocialDynamicsService();

$pipeline = new WorldEvolutionPipeline($cosmicService, $bifurcationManager, $socialDynamicsService);

// 2. Setup Initial State
$civ = CivilizationState::defaultObservation(0);
// Find merchant class
$merchants = null;
foreach ($civ->socialClasses as $c) {
    if ($c->type === SocialClassType::MERCHANT) $merchants = $c;
}

echo "Initial Merchant Power: " . $merchants->power . "\n";

// 3. Setup Mock Behavior
$cosmicService->shouldReceive('step')->andReturn(new CosmicState(0.2, 0.5, 0.1, 0.0, 0.9, 'EQUILIBRIUM', 100));
$bifurcationManager->shouldReceive('evaluate')->andReturn(['bifurcated' => false]);

// 4. Create high tech snapshot to boost merchants
$env = EnvironmentState::defaultObservation(0);
$current = new WorldSnapshot(
    cosmic: new CosmicState(0.2, 0.5, 0.1, 0.0, 0.9, 'EQUILIBRIUM', 0),
    environment: $env,
    civilization: new CivilizationState(
        collectiveKnowledge: 0.5,
        ritualCoherence: 0.5,
        technologicalLevel: 1.8, // High tech
        factionStability: 0.8,
        resonanceAccumulator: 0.0,
        resilience: 1.0,
        year: 0,
        socialClasses: $civ->socialClasses
    ),
    year: 0
);

// 5. Evolve
echo "Evolving for 50 years...\n";
$next = $pipeline->step($current, 0.0, 50);

// 6. Verify Merchant Rise
$nextMerchants = null;
foreach ($next->civilization->socialClasses as $c) {
    if ($c->type === SocialClassType::MERCHANT) $nextMerchants = $c;
}

echo "Final Merchant Power: " . $nextMerchants->power . "\n";

if ($nextMerchants->power > $merchants->power) {
    echo "SUCCESS: Merchant power increased due to high tech!\n";
} else {
    echo "FAILURE: Merchant power did not increase.\n";
}

// 7. Verify Event Detection (Partial check via internal logic)
// In a real verification we would test SagaRunner directly, but here we can check if conditions met
if ($nextMerchants->power > 0.3) {
    echo "Condition for Merchant Event approaching...\n";
}

Mockery::close();
