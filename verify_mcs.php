<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmic\Services\WorldEvolutionPipeline;
use App\Domains\Cosmic\Services\PhaseEngine;
use App\Domains\Cosmic\Services\EventEngine;
use App\Domains\Cosmic\Services\HeroAttractor;
use App\Domains\Saga\CausalNarrativeAssembler;

// Mock setup if not in full Laravel environment, but for ad-hoc script we assume bootstrap
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STARTING DRAMA-FIRST SIMULATION VERIFICATION ---\n";

// 1. Setup Engine and Services
$phaseEngine = new PhaseEngine();
$eventEngine = new EventEngine();
$heroAttractor = new HeroAttractor();

$pipeline = new WorldEvolutionPipeline(
    app(App\Domains\Cosmic\Services\CosmicEvolutionService::class),
    app(App\Domains\Cosmic\Services\BifurcationManager::class),
    app(App\Domains\Cosmic\Services\SocialDynamicsService::class),
    $phaseEngine,
    $eventEngine,
    $heroAttractor
);

$assembler = app(CausalNarrativeAssembler::class); // Will use its internal PhaseEngine but that's fine

// 2. Initial State
$civ = CivilizationState::defaultObservation(0);
$env = EnvironmentState::defaultObservation(0);
$cosmic = new CosmicState(0.3, 0.5, 0.1, 0.0, 0.8, 'EQUILIBRIUM', 0);

$currentSnapshot = new WorldSnapshot($cosmic, $env, $civ, 0);

for ($i = 1; $i <= 10; $i++) {
    $prevCiv = $currentSnapshot->civilization;
    
    // Step forward 50 years to see significant drift
    $nextSnapshot = $pipeline->step($currentSnapshot, 0.0, 50); 
    $events = $pipeline->getLastStepEvents();
    
    $civ = $nextSnapshot->civilization;
    $phase = $phaseEngine->determinePhase($civ);
    
    echo "\n[Year " . $nextSnapshot->year . "] Phase: " . strtoupper($phase) . "\n";
    echo "Prosperity: " . round($civ->prosperity, 4) . " | Stability: " . round($civ->stability, 4) . "\n";
    echo "Entropy:    " . round($civ->internalEntropy, 4) . " | Military: " . round($civ->militaryPressure, 4) . "\n";
    echo "Cultural:   " . round($civ->culturalEnergy, 4) . " | Phase Years: " . $civ->yearsInPhase . "\n";
    
    echo "Events Generated: " . count($events) . "\n";
    foreach ($events as $event) {
        $name = $event['type'] ?? 'unknown';
        $intensity = $event['intensity'] ?? 0;
        $success = ($event['success'] ?? false) ? 'YES' : 'NO';
        echo " - [$name] Intensity: $intensity | Success: $success\n";
        if (isset($event['archetype'])) {
            echo "   -> Hero Spawned: " . $event['archetype'] . " (Charisma: " . $event['charisma'] . ")\n";
        }
    }
    
    $narrative = $assembler->assemble($events, $i, $nextSnapshot->cosmic, $civ, $prevCiv);
    echo "Narrative Fragment:\n" . substr($narrative, 0, 500) . "...\n";
    
    $currentSnapshot = $nextSnapshot;
    echo "---------------------------------------------------\n";
}

echo "\n--- VERIFICATION COMPLETE ---\n";
