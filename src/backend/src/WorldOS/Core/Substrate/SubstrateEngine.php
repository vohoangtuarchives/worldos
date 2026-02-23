<?php

declare(strict_types=1);

namespace WorldOS\Core\Substrate;

use WorldOS\Core\ValueObject\SubstrateVector;

/**
 * SubstrateEngine: Extracts the hidden laws from the sealed hash.
 * Completely deterministic and sealed. 
 */
class SubstrateEngine
{
    public function __construct(
        private readonly string $substrateHash
    ) {
    }

    /**
     * Derives a SubstrateVector for a specific point in simulation time.
     */
    public function getModifiers(int $simulationTime): SubstrateVector
    {
        // 1. Create a unique phase for this specific time
        // We use hashing to ensure there's no visible pattern in the numbers.
        $phase = hash('sha256', $this->substrateHash . '|' . $simulationTime);

        // 2. Parse the hash into modifiers (0.8 to 1.2 range for subtle influence)
        return new SubstrateVector(
            $this->deriveModifier($phase, 0, 0.8, 1.2), // entropyDissipation
            $this->deriveModifier($phase, 8, 0.5, 1.5), // birthPressure (wider range)
            $this->deriveModifier($phase, 16, 0.7, 1.3), // mutationIntensity
            $this->deriveModifier($phase, 24, 0.9, 1.4), // fragilityModifier
            $this->deriveModifier($phase, 32, 0.1, 2.0), // transcendenceAccess (rare spikes)
            [
                $this->deriveModifier($phase, 40, -1.0, 1.0), // seed 1
                $this->deriveModifier($phase, 48, -1.0, 1.0), // seed 2
                $this->deriveModifier($phase, 56, -1.0, 1.0), // seed 3
            ]
        );
    }

    /**
     * Extracts a deterministic float from a hash segment.
     */
    private function deriveModifier(string $hash, int $start, float $min, float $max): float
    {
        // Extract 8 hex chars (4 bytes)
        $segment = substr($hash, $start, 8);
        $value = hexdec($segment);
        
        // Normalize to 0.0 - 1.0
        $normalized = $value / 0xFFFFFFFF;

        // Scale to range
        return $min + ($normalized * ($max - $min));
    }
}
