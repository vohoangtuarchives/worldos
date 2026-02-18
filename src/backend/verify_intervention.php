<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\InterventionService;

require __DIR__ . '/vendor/autoload.php';

$service = new InterventionService();

// Create Test Universe
$v1 = WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5);
$universe = new Universe($v1, [], 'test-univ');

echo "Initial State:\n";
echo "Order: " . $universe->getState()->getOrder() . "\n";
echo "Entropy: " . $universe->getState()->getEntropy() . "\n";
echo "Cohesion: " . $universe->getState()->getCohesion() . "\n";

// Test STABILIZE
echo "\n--- TESTING STABILIZE ---\n";
$stabilized = $service->intervene($universe, InterventionService::TYPE_STABILIZE, 0.2);
echo "New Order: " . $stabilized->getState()->getOrder() . "\n";
echo "New Entropy: " . $stabilized->getState()->getEntropy() . "\n";

if ($stabilized->getState()->getOrder() > 0.5 && $stabilized->getState()->getEntropy() < 0.5) {
    echo "PASS: Stabilize worked.\n";
} else {
    echo "FAIL: Stabilize failed.\n";
}

// Test DISRUPT
echo "\n--- TESTING DISRUPT ---\n";
$disrupted = $service->intervene($universe, InterventionService::TYPE_DISRUPT, 0.2);
echo "New Order: " . $disrupted->getState()->getOrder() . "\n";
echo "New Entropy: " . $disrupted->getState()->getEntropy() . "\n";

if ($disrupted->getState()->getOrder() < 0.5 && $disrupted->getState()->getEntropy() > 0.5) {
    echo "PASS: Disrupt worked.\n";
} else {
    echo "FAIL: Disrupt failed.\n";
}
