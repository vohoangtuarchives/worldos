<?php

namespace Tuzy\Domain\World\Policies;

use Tuzy\Domain\World\Contracts\WorldInvariantInterface;
use App\Models\World;

class StandardWorldInvariant implements WorldInvariantInterface
{
    private ?string $violationMessage = null;

    public function validate(World $world): bool
    {
        $profile = $world->physics_profile;

        // 1. Entropy validation
        if ($world->entropy < 0) {
            $this->violationMessage = "Negative entropy detected: {$world->entropy}";
            return false;
        }

        // 2. Resource validation (Context dependent, checks essential resources)
        // For now, assume resources are stored in a dedicated table or JSON, 
        // but here we check for invalid negative values if they exist in stats
        // Placeholder for future Resource Aggregate integration

        // 3. Physics Profile integrity
        if ($profile->instability_rate < 0 || $profile->decay_rate < 0) {
             $this->violationMessage = "Invalid physics constants detected";
             return false;
        }

        // 4. Time validation
        if ($world->tick < 0) {
            $this->violationMessage = "Negative time tick detected";
            return false;
        }

        return true;
    }

    public function getViolationMessage(): ?string
    {
        return $this->violationMessage;
    }
}
