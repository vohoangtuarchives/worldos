<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Policy;

use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;

/**
 * CompiledPolicy represents the post-compilation immutable ruleset.
 * Consumed directly by EvolutionOperator during tick simulations.
 *
 * V5 upgrade: `spectralRadius float` replaced by `CouplingMatrix`
 * to enable full 17×17 cross-dimensional coupling.
 */
final class CompiledPolicy
{
    private function __construct(
        private readonly string         $kernelVersion,
        private readonly float          $chaosFactor,
        private readonly CouplingMatrix $couplingMatrix,
        private readonly float          $mutationStrength,
        private readonly int            $maxActiveBranches,
        private readonly \Closure       $weightFormulaExecutable
    ) {}

    public static function create(
        string         $kernelVersion,
        float          $chaosFactor,
        CouplingMatrix $couplingMatrix,
        float          $mutationStrength,
        int            $maxActiveBranches,
        \Closure       $weightFormulaExecutable
    ): self {
        return new self(
            $kernelVersion,
            $chaosFactor,
            $couplingMatrix,
            $mutationStrength,
            $maxActiveBranches,
            $weightFormulaExecutable
        );
    }

    /**
     * Convenience factory — builds a policy around an identity matrix.
     * Useful for tests and baseline runs without a full World configuration.
     */
    public static function baseline(
        string  $kernelVersion = 'v5-baseline',
        float   $chaosFactor   = 0.02,
        float   $spectralRadius = 0.98
    ): self {
        return new self(
            $kernelVersion,
            $chaosFactor,
            CouplingMatrix::identity($spectralRadius),
            mutationStrength:      0.01,
            maxActiveBranches:     8,
            weightFormulaExecutable: static fn(array $ctx): float =>
                max(0.0, min(1.0,
                    ($ctx['w']       ?? 0.5) * 0.5
                    - ($ctx['anomaly']       ?? 0.0) * 0.3
                    + ($ctx['richness']      ?? 0.0) * 0.2
                    - ($ctx['entropy_decay'] ?? 0.0) * 0.1
                ))
        );
    }

    // ------------------------------------------------------------------
    // Getters
    // ------------------------------------------------------------------

    public function getKernelVersion(): string
    {
        return $this->kernelVersion;
    }

    public function getChaosFactor(): float
    {
        return $this->chaosFactor;
    }

    public function getCouplingMatrix(): CouplingMatrix
    {
        return $this->couplingMatrix;
    }

    /**
     * @deprecated Use getCouplingMatrix()->getSpectralRadius() instead.
     * Kept for backward compatibility with tests that still call this.
     */
    public function getSpectralRadius(): float
    {
        return $this->couplingMatrix->getSpectralRadius();
    }

    public function getMutationStrength(): float
    {
        return $this->mutationStrength;
    }

    public function getMaxActiveBranches(): int
    {
        return $this->maxActiveBranches;
    }

    /**
     * Executes the compiled weight formula with provided context.
     */
    public function evaluateWeight(array $context): float
    {
        return ($this->weightFormulaExecutable)($context);
    }
}
