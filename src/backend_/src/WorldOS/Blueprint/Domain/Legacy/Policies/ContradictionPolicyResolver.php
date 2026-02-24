<?php

namespace WorldOS\Blueprint\Domain\Legacy\Policies;

use Symfony\Component\Yaml\Yaml;

class ContradictionPolicyResolver
{
    private array $policy;

    public function __construct(string $policyPath = null)
    {
        $path = $policyPath ?? base_path('world/policies/ContradictionResolution.yaml');
        if (file_exists($path)) {
            $this->policy = Yaml::parseFile($path);
        } else {
            $this->policy = [];
        }
    }

    public function getBudget(): int
    {
        return $this->policy['policy']['budget']['max_active_contradictions'] ?? 3;
    }

    public function getStrategy(string $severity, bool $gateBlocked): ?array
    {
        $strategies = $this->policy['policy']['strategies'] ?? [];

        // Simple logic selection based on policy definitions
        if ($gateBlocked && isset($strategies['deflection'])) {
            return $strategies['deflection'];
        }

        if ($severity === 'critical' && isset($strategies['sacrifice'])) {
            return $strategies['sacrifice'];
        }

        if (isset($strategies['accumulation'])) {
             return $strategies['accumulation'];
        }

        return null;
    }

    public function isActionForbidden(string $action): bool
    {
        $forbidden = $this->policy['policy']['forbidden_actions'] ?? [];
        return in_array($action, $forbidden);
    }
}
