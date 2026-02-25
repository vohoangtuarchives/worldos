<?php

declare(strict_types=1);

namespace App\Infrastructure\Kernel;

use Illuminate\Support\Facades\DB;
use App\Domain\Kernel\Reports\StabilityReport;

/**
 * Persists the experiment configuration, results, metrics, 
 * and ML features to the database.
 */
class ExperimentRepository
{
    /**
     * Initializes a new experiment run and stores immutable basic parameters.
     */
    public function createExperiment(string $id, array $config): void
    {
        DB::table('kernel_experiments')->insert([
            'id' => $id,
            'kernel_version' => $config['kernel_version'] ?? '1.2.0',
            'commit_hash' => $config['commit_hash'] ?? null,
            'n_dimension' => $config['n_dimension'],
            'n_regions' => $config['n_regions'] ?? 1,
            'alpha' => $config['alpha'],
            'beta' => $config['beta'],
            'lambda' => $config['lambda'],
            'eta' => $config['eta'],
            'gamma_cap' => $config['gamma_cap'],
            'delta_target' => $config['delta_target'],
            'init_method' => $config['init_method'] ?? 'zero',
            'random_seed' => $config['random_seed'] ?? null,
            'x0_hash' => $config['x0_hash'] ?? null,
            'precision_mode' => $config['precision_mode'] ?? 'float64',
            'hardware_spec' => $config['hardware_spec'] ?? null,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Completes an experiment and stores final results/classifications.
     */
    public function completeExperiment(string $id, StabilityReport $report, array $performance, string $finalHash, string $classification): void
    {
        DB::table('kernel_experiments')
            ->where('id', $id)
            ->update([
                'spectral_radius' => $report->spectralRadius,
                'margin' => $report->margin,
                'max_norm' => $performance['max_norm'] ?? null,
                'gershgorin_max_bound' => $report->maxGershgorinBound,
                'classification' => $classification,
                'tick_count' => $performance['tick_count'] ?? 0,
                'avg_time_per_tick_ms' => $performance['avg_time_per_tick_ms'] ?? 0.0,
                'max_time_per_tick_ms' => $performance['max_time_per_tick_ms'] ?? 0.0,
                'memory_peak_mb' => $performance['memory_peak_mb'] ?? 0,
                'total_runtime_ms' => $performance['total_runtime_ms'] ?? 0,
                'stability_violations' => $performance['violations_count'] ?? 0,
                'final_snapshot_hash' => $finalHash,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
    }

    /**
     * Rejects an experiment due to violation.
     */
    public function rejectExperiment(string $id, string $reason, array $performance): void
    {
        DB::table('kernel_experiments')
            ->where('id', $id)
            ->update([
                'classification' => 'rejected',
                'status' => 'rejected',
                'tick_count' => $performance['tick_count'] ?? 0,
                'total_runtime_ms' => $performance['total_runtime_ms'] ?? 0,
                'stability_violations' => ($performance['violations_count'] ?? 0) + 1, // at least 1 for rejection
                'completed_at' => now(),
            ]);
    }

    /**
     * Record time-series metrics per tick.
     */
    public function storeMetrics(string $experimentId, int $tick, float $stateNorm, float $ratioR, float $gershgorinBound = null, int $violations = 0): void
    {
        DB::table('kernel_experiment_metrics')->insert([
            'experiment_id' => $experimentId,
            'tick' => $tick,
            'state_norm' => $stateNorm,
            'ratio_r' => $ratioR,
            'gershgorin_bound' => $gershgorinBound,
            'violations_count' => $violations,
            'created_at' => now(),
        ]);
    }

    /**
     * Store final features for ML Stability prediction layer.
     */
    public function storeStabilityFeatures(string $experimentId, array $features): void
    {
        DB::table('kernel_stability_features')->insert(array_merge(
            ['experiment_id' => $experimentId, 'created_at' => now()],
            $features
        ));
    }
}
