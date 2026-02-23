<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Compiler;

use WorldOS\Kernel\Domain\Policy\KernelPolicy;
use InvalidArgumentException;

/**
 * Validates the KernelPolicy (raw DSL) before allowing it to be compiled.
 * Ensures strict constraints to prevent the multiverse simulation from collapsing or exploding.
 */
final class PolicyValidator
{
    public const MAX_CHAOS_FACTOR = 0.05;
    public const MIN_SPECTRAL_RADIUS = 0.90;
    public const MAX_SPECTRAL_RADIUS = 0.999;
    public const MAX_MUTATION_STRENGTH = 0.10;
    
    /**
     * @throws InvalidArgumentException if the policy violates stability rules
     */
    public function validate(KernelPolicy $policy): void
    {
        $this->validateStabilityBounds($policy->getStabilityBounds());
        $this->validateEvolutionRules($policy->getEvolutionRules());
        $this->validateForkMechanics($policy->getForkMechanics());
        $this->validateWeightFormulas($policy->getWeightFormulas());
    }

    private function validateStabilityBounds(array $stability): void
    {
        if (isset($stability['chaos_factor']) && $stability['chaos_factor'] > self::MAX_CHAOS_FACTOR) {
            throw new InvalidArgumentException(sprintf("Chaos factor exceeds maximum safety limit of %s.", self::MAX_CHAOS_FACTOR));
        }

        if (isset($stability['spectral_radius'])) {
            $radius = $stability['spectral_radius'];
            if ($radius < self::MIN_SPECTRAL_RADIUS || $radius > self::MAX_SPECTRAL_RADIUS) {
                throw new InvalidArgumentException(sprintf("Spectral radius must be between %s and %s.", self::MIN_SPECTRAL_RADIUS, self::MAX_SPECTRAL_RADIUS));
            }
        }
    }

    private function validateEvolutionRules(array $evolution): void
    {
        if (isset($evolution['mutation_strength']) && $evolution['mutation_strength'] > self::MAX_MUTATION_STRENGTH) {
            throw new InvalidArgumentException(sprintf("Mutation strength exceeds absolute maximum of %s.", self::MAX_MUTATION_STRENGTH));
        }
    }

    private function validateForkMechanics(array $fork): void
    {
        if (isset($fork['max_active_branches']) && $fork['max_active_branches'] <= 0) {
            throw new InvalidArgumentException("Max active branches must be greater than zero.");
        }
    }

    private function validateWeightFormulas(array $weight): void
    {
        if (empty($weight['formula'])) {
            throw new InvalidArgumentException("Weight formula is missing from the DSL.");
        }
        
        // Basic static security check: block dangerous generic evaluation constructs
        $dangerousTokens = ['eval', 'exec', 'system', 'passthru', 'shell_exec'];
        foreach ($dangerousTokens as $token) {
            if (stripos($weight['formula'], $token) !== false) {
                throw new InvalidArgumentException("Weight formula contains forbidden tokens.");
            }
        }
    }
}
