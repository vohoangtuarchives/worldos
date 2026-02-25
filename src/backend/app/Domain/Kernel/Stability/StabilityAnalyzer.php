<?php

declare(strict_types=1);

namespace App\Domain\Kernel\Stability;

use App\Domain\Kernel\Matrix\MatrixOperator;
use App\Domain\Kernel\Reports\StabilityReport;

/**
 * Orchestrator cho stability checks đảm bảo mọi tham số pass các bất biến của GovernanceGuard.
 */
final class StabilityAnalyzer
{
    private GershgorinAnalyzer $gershgorin;
    private SpectralEstimator $spectral;

    public function __construct(
        GershgorinAnalyzer $gershgorin,
        SpectralEstimator $spectral
    ) {
        $this->gershgorin = $gershgorin;
        $this->spectral = $spectral;
    }

    /**
     * @param MatrixOperator $J Bounded dynamic space operator
     * @param float          $delta Minimal configured margin threshold
     * @return StabilityReport Immutable validation output.
     */
    public function analyze(MatrixOperator $J, float $delta): StabilityReport
    {
        $gershgorinResult = $this->gershgorin->verify($J);
        $rho = $this->spectral->estimate($J);

        $margin = 1.0 - $rho;
        
        // Cần cả 2 điều kiện: Estimated rho cho biết xu hướng phổ, Gershgorin ngăn cản ma trận blow up.
        $isContractive = ($rho <= (1.0 - $delta)) && empty($gershgorinResult->violations);

        return new StabilityReport(
            $rho,
            $margin,
            $gershgorinResult->maxBound,
            $gershgorinResult->violations,
            $isContractive
        );
    }
}
