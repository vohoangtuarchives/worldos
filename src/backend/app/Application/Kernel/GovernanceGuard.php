<?php

declare(strict_types=1);

namespace App\Application\Kernel;

use App\Domain\Kernel\Matrix\KernelMatrixBuilder;
use App\Domain\Kernel\Stability\StabilityAnalyzer;
use App\Domain\Kernel\Exception\KernelMathException;
use App\Domain\Kernel\Stability\StabilityBudgetMonitor;
use App\Domain\Kernel\Reports\StabilityReport;

/**
 * Enforces the Constitution of WorldOS *before* and *after* 
 * passing data to the Deterministic MathCore.
 * Has the authority to reject and abort experiments.
 */
final class GovernanceGuard
{
    private StabilityAnalyzer $analyzer;
    private StabilityBudgetMonitor $budgetMonitor;

    public function __construct(
        StabilityAnalyzer $analyzer,
        StabilityBudgetMonitor $budgetMonitor
    ) {
        $this->analyzer = $analyzer;
        $this->budgetMonitor = $budgetMonitor;
    }

    /**
     * Checks all static parameters and input limits before computing tick.
     * @throws KernelMathException on any variant violation.
     */
    public function authorizeTick(
        KernelMatrixBuilder $J,
        array $u,
        float $gammaCap,
        float $deltaTarget,
        float $eta,
        float $alpha
    ): StabilityReport {
        // [Invariant 2.2] Intrinsic Damping Invariant
        if ($eta <= 0.0) {
            throw new KernelMathException("Invariant Violation [2.2]: Intrinsic damping (eta) must be strictly positive. Received: {$eta}");
        }
        if (($alpha * $eta) >= 1.0) {
            throw new KernelMathException("Invariant Violation [2.2]: Damping overshoot risk. alpha * eta must be < 1.");
        }

        // [Invariant 3.1] Input Norm Bound
        $inputNorm = $this->calculateNorm($u);
        if ($inputNorm > $gammaCap) {
            throw new KernelMathException("Invariant Violation [3.1]: Input norm ({$inputNorm}) exceeds gamma_cap ({$gammaCap}).");
        }

        // [Invariant 2.1] Spectral Margin Invariant via Gershgorin and Power iteration.
        $report = $this->analyzer->analyze($J, $deltaTarget);
        if (!$report->isContractive) {
            throw new KernelMathException("Invariant Violation [2.1]: System state lost strict contraction. Target Delta: {$deltaTarget}. Estimated Rho: {$report->spectralRadius}.");
        }
        
        return $report;
    }

    /**
     * Valuates the Stability Budget post-MathCore calculation.
     * Prevents ratio explosion.
     *
     * @throws KernelMathException
     */
    public function inspectResult(array $xPrev, array $xNext, float $deltaTarget, float $epsilon = 0.01): float
    {
        // [Invariant 3.2] Stability Budget Monitor
        $ratio = $this->budgetMonitor->check($xPrev, $xNext);
        $budgetLimit = 1.0 - $deltaTarget + $epsilon;

        if ($ratio > $budgetLimit) {
            throw new KernelMathException("Invariant Violation [3.2]: Stability budget exceeded limit. Ratio: {$ratio}, Allowed: {$budgetLimit}.");
        }

        return $ratio;
    }

    private function calculateNorm(array $v): float
    {
        $sumSq = 0.0;
        foreach ($v as $val) {
            $sumSq += $val * $val;
        }
        return sqrt($sumSq);
    }
}
