<?php

use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING THERMODYNAMIC COUPLING ---\n";

// Initial State: Meta-stable but dangerous
// Entropy > 0.6 (Critical Zone)
// Resilience High (System can fight back for a while)
$cosmic = new CosmicState(
    entropy: 0.7, 
    energy: 0.5, 
    causality: 0.1, 
    strain: 0.1, 
    stability: 0.3, 
    currentAttractor: 'EQUILIBRIUM', 
    year: 1000
);

$civ = CivilizationState::defaultObservation(1000);
// Override resilience to 0.9
$civ = new CivilizationState(
    collectiveKnowledge: $civ->collectiveKnowledge,
    ritualCoherence: $civ->ritualCoherence,
    technologicalLevel: $civ->technologicalLevel,
    factionStability: $civ->factionStability,
    resonanceAccumulator: $civ->resonanceAccumulator,
    resilience: 0.9,
    year: 1000
);

$env = EnvironmentState::defaultObservation(1000);

echo sprintf("%-6s | %-8s | %-8s | %-8s | %-8s | %-8s\n", "Year", "Entropy", "Strain", "Resil", "Effic", "Status");
echo str_repeat("-", 60) . "\n";

// Run for 50 years (External Time) -> 50 loops internal
$deltaYears = 50; 
// But we want to see year-by-year output, so we loop manually here for visualization
// The internal loop in evolve() handles numerical stability for whatever delta we pass.
// Let's step 1 year at a time to see the curve.

for ($i = 0; $i <= 50; $i++) {
    $efficiency = ($cosmic->entropy > 0.6) ? exp(-5.0 * pow($cosmic->entropy - 0.6, 2)) : 1.0;
    
    $status = "OK";
    if ($cosmic->isCritical($civ->resilience)) {
        $status = "COLLAPSE";
    } elseif ($civ->resilience < 0.3) {
        $status = "CRITICAL";
    } elseif ($cosmic->strain > 0.5) {
        $status = "STRAINED";
    }

    echo sprintf("%-6d | %-8.4f | %-8.4f | %-8.4f | %-8.4f | %s\n", 
        $cosmic->year, 
        $cosmic->entropy, 
        $cosmic->strain, 
        $civ->resilience,
        $efficiency,
        $status
    );

    if ($status === "COLLAPSE") {
        echo "!!! SYSTEM COLLAPSED AT YEAR {$cosmic->year} !!!\n";
        break;
    }

    // Evolve 1 step
    $nextCosmic = $cosmic->evolve(0.5, $civ->getResonanceFeedback(), 1);
    $nextEnv = $env->evolve($nextCosmic, $civ->environmentalImpact(), 1);
    $nextCiv = $civ->evolve($nextEnv, $nextCosmic, 0.0, 1);

    $cosmic = $nextCosmic;
    $env = $nextEnv;
    $civ = $nextCiv;
}
