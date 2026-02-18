<?php

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Services\CouplingService;

require __DIR__ . '/vendor/autoload.php';

$service = new CouplingService();

// Create Target Universe (Low Entropy)
$v1 = WorldStateVector::create(0.9, 0.1, 0.8, 0.9, 0.5, 0.5, 0.1, 0.0);
$target = new Universe($v1, [], 'target-univ');

// Create Neighbor (High Entropy, Close Distance)
// Distance depends on all dims. Let's make it very similar but High Entropy.
// v1 order 0.9. Let's make v2 order 0.8.
// v1 entropy 0.1. Let's make v2 entropy 0.9 (Critical!)
$v2 = WorldStateVector::create(0.85, 0.9, 0.8, 0.9, 0.5, 0.5, 0.1, 0.0);
$neighbor = new Universe($v2, [], 'chaos-univ');

echo "Target Entropy: " . $target->getState()->getEntropy() . "\n";
echo "Neighbor Entropy: " . $neighbor->getState()->getEntropy() . "\n";

$dist = $service->calculateDistance($target, $neighbor);
echo "Distance: " . $dist . "\n";

// Interact
$result = $service->interact($target, [$neighbor], 0.2); // Strong coupling

if ($result) {
    echo "COUPLING OCCURRED!\n";
    echo "New Target Entropy: " . $result->getEntropy() . "\n";
    echo "New Target Trauma: " . $result->getTrauma() . "\n";
    
    if ($result->getEntropy() > 0.1) {
        echo "TEST PASSED: Entropy increased.\n";
    } else {
        echo "TEST FAILED: Entropy did not increase.\n";
    }
} else {
    echo "TEST FAILED: No interaction occurred.\n";
}
