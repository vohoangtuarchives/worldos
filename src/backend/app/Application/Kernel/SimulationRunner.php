<?php

declare(strict_types=1);

namespace App\Application\Kernel;

use App\Domain\Kernel\MathCore;
use App\Domain\Kernel\Matrix\KernelMatrixBuilder;
use App\Infrastructure\Kernel\HashChainService;
use App\Infrastructure\Kernel\ExperimentRepository;
use App\Infrastructure\Kernel\SnapshotRepository;
use Throwable;

/**
 * Orchestrates the full simulation loop.
 * - Loads parameters
 * - Passes Governance Guard
 * - Computes step via MathCore
 * - Persists via infrastructure
 */
final class SimulationRunner
{
    private GovernanceGuard $guard;
    private MathCore $mathCore;
    private HashChainService $hashChain;
    private ExperimentRepository $experimentRepo;
    private SnapshotRepository $snapshotRepo;

    public function __construct(
        GovernanceGuard $guard,
        MathCore $mathCore,
        HashChainService $hashChain,
        ExperimentRepository $experimentRepo,
        SnapshotRepository $snapshotRepo
    ) {
        $this->guard = $guard;
        $this->mathCore = $mathCore;
        $this->hashChain = $hashChain;
        $this->experimentRepo = $experimentRepo;
        $this->snapshotRepo = $snapshotRepo;
    }

    /**
     * Executes the simulation safely.
     * Ensure random seeds are explicitly passed for reproducibility.
     */
    public function runExperiment(string $experimentId, array $config, array $initialX): void
    {
        $this->experimentRepo->createExperiment($experimentId, $config);
        
        $n = $config['n_dimension'];
        $alpha = $config['alpha'];
        $lambda = $config['lambda'];
        $eta = $config['eta'];
        $beta = $config['beta'];
        $gammaCap = $config['gamma_cap'];
        $deltaTarget = $config['delta_target'];
        
        $A = $config['A'] ?? $this->buildIdentityMatrix($n);
        $L = $config['L'] ?? [];
        $ticks = $config['tick_count'] ?? 1000;
        
        // Genesis Hash
        $currentHash = $this->hashChain->generateGenesisHash($initialX);
        $x = $initialX;

        $startTime = microtime(true);
        $violations = 0;
        $maxNorm = 0.0;
        
        try {
            for ($t = 1; $t <= $ticks; $t++) {
                $tickStart = microtime(true);

                // External forces - should be deterministic or derived from plugin ordering
                $u = array_fill(0, $n, 0.0); // Simplified deterministic constant 0 input. 

                $J = new KernelMatrixBuilder($A, $L, $alpha, $lambda, $eta);

                // Phase 1: Pre-calculation Governance Check (Spectral Margin, Bounds, etc.)
                $report = $this->guard->authorizeTick($J, $u, $gammaCap, $deltaTarget, $eta, $alpha);

                // Phase 2: Compute True Simulation Step. Pure Functionality.
                $xNext = $this->mathCore->step($x, $u, $A, $L, $alpha, $lambda, $eta, $beta);

                // Phase 3: Post-calculation Governance Check (Stability Budget ratio limits)
                $rRatio = $this->guard->inspectResult($x, $xNext, $deltaTarget);

                // Phase 4: Construct Audit Trail Hash Chain
                $nextHash = $this->hashChain->generateNextHash($currentHash, $xNext);

                // Phase 5: Persistence & Metrics Layer
                $normX = $this->mathCore->norm($xNext);
                $maxNorm = max($maxNorm, $normX);

                $this->snapshotRepo->storeSnapshot(
                    $experimentId,
                    $t,
                    $xNext,
                    $u,
                    compact('alpha', 'lambda', 'eta', 'beta'),
                    [], // RNG states typically tracked via plugins
                    $nextHash,
                    $currentHash
                );

                $this->experimentRepo->storeMetrics(
                    $experimentId, 
                    $t, 
                    $normX, 
                    $rRatio, 
                    $report->maxGershgorinBound, 
                    count($report->gershgorinViolations)
                );

                $x = $xNext;
                $currentHash = $nextHash;

                // Stop if execution took too long (just for sample implementation, usually benchmark tracks it)
            }

            // After simulation loop success... Write ML features and Finalize.
            $this->experimentRepo->storeStabilityFeatures($experimentId, [
                'dimension_n' => $n,
                'gershgorin_max_bound' => $report->maxGershgorinBound,
                'spectral_gap_estimate' => $report->spectralRadius,
            ]);

            $totalRuntime = (microtime(true) - $startTime) * 1000;

            $this->experimentRepo->completeExperiment($experimentId, $report, [
                'tick_count' => $ticks,
                'max_norm' => $maxNorm,
                'avg_time_per_tick_ms' => $totalRuntime / $ticks,
                'total_runtime_ms' => $totalRuntime,
                'violations_count' => $violations
            ], $currentHash, 'convergent');

        } catch (Throwable $e) {
            // Unhandled Divergence or Mathematical Policy Rejection
            $this->experimentRepo->rejectExperiment($experimentId, $e->getMessage(), [
                'tick_count' => $t ?? 0,
                'total_runtime_ms' => (microtime(true) - $startTime) * 1000
            ]);
        }
    }

    private function buildIdentityMatrix(int $n): array
    {
        $A = [];
        for ($i = 0; $i < $n; $i++) {
            $A[$i] = array_fill(0, $n, 0.0);
            $A[$i][$i] = 1.0;
        }
        return $A;
    }
}
