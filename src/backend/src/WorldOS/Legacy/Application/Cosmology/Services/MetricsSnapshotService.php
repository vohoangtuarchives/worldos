<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Domain\Cosmology\Aggregates\AttractorAggregate;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\IndividualMemory;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\InteractionMemory;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\MetricsSnapshot;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\WorldSnapshot;

/**
 * MetricsSnapshotService — computes all 18 KPIs from simulation state.
 *
 * Called every epoch close to produce a MetricsSnapshot.
 * Pure computation, no side effects.
 */
class MetricsSnapshotService
{
    // CHS weights
    private const CHS_W1 = 0.25; // (1 - SSI) — stability reward
    private const CHS_W2 = 0.20; // DI — diversity reward
    private const CHS_W3 = 0.15; // AGR — adaptation reward
    private const CHS_W4 = 0.10; // RES — rebirth effectiveness
    private const CHS_W5 = 0.15; // ICR — concentration penalty
    private const CHS_W6 = 0.15; // CF — collapse penalty

    /**
     * Calculate full metrics snapshot.
     *
     * @param WorldSnapshot $current Current world state
     * @param WorldSnapshot[] $trajectory Recent trajectory (50-100 snapshots)
     * @param AttractorAggregate[] $attractors Active attractor aggregates
     * @param IndividualMemory[] $memories Individual memories per attractor
     * @param InteractionMemory[] $interactions Interaction memories (pairwise)
     * @param float $gcmfMagnitude Current |GCMF|
     * @param array $governanceStats { proposals: int, approved: int, interventions: int, avg_latency: float }
     * @param int $collapseCount Collapses in last 50 epochs
     * @param int $newArchetypeCount New archetypes in last 100 epochs
     */
    public function calculate(
        WorldSnapshot $current,
        array $trajectory,
        array $attractors = [],
        array $memories = [],
        array $interactions = [],
        float $gcmfMagnitude = 0.0,
        array $governanceStats = [],
        int $collapseCount = 0,
        int $newArchetypeCount = 0,
    ): MetricsSnapshot {
        // --- STABILITY ---
        $ssi = $this->calculateSSI($current, $trajectory);
        $cf = $collapseCount / max(1, min(50, count($trajectory)));
        $stabilityMargin = $this->calculateStabilityMargin($current);

        // --- EVOLUTION ---
        $influences = $this->calculateInfluences($attractors);
        $di = $this->calculateDiversityIndex($influences);
        $agr = $this->calculateAdaptationGainRate($trajectory);
        $res = $this->calculateRebirthEffectiveness($attractors);

        // --- POWER ---
        $icr = !empty($influences) ? max($influences) : 0.0;
        $vps = $this->calculateVotingPowerSkew($attractors);
        $api = $this->calculateAlliancePolarization($interactions);

        // --- MEMORY ---
        $hbr = $this->calculateHistoricalBiasRatio($memories);
        $cm = $gcmfMagnitude;
        $mde = $this->calculateMemoryDecayEffectiveness($memories);

        // --- EMERGENCE ---
        $epi = $this->calculateEmergencePressure($current, $di, $gcmfMagnitude);
        $atr = $newArchetypeCount / max(1, min(100, count($trajectory)));

        // --- GOVERNANCE ---
        $totalProposals = $governanceStats['proposals'] ?? 0;
        $approved = $governanceStats['approved'] ?? 0;
        $par = $totalProposals > 0 ? $approved / $totalProposals : 0.0;
        $gl = (float)($governanceStats['avg_latency'] ?? 0.0);
        $interventions = $governanceStats['interventions'] ?? 0;
        $hii = $interventions / max(1, min(50, count($trajectory)));

        // --- META: CHS ---
        $chs = $this->calculateCHS($ssi, $di, $agr, $res, $icr, $cf);

        return new MetricsSnapshot(
            epoch: $current->year,
            spectralStabilityIndex: round($ssi, 4),
            collapseFrequency: round($cf, 4),
            stabilityMargin: round($stabilityMargin, 4),
            diversityIndex: round($di, 4),
            adaptationGainRate: round($agr, 4),
            rebirthEffectiveness: round($res, 4),
            influenceConcentration: round($icr, 4),
            votingPowerSkew: round($vps, 4),
            alliancePolarization: round($api, 4),
            historicalBiasRatio: round($hbr, 4),
            collectiveMomentum: round($cm, 4),
            memoryDecayEffectiveness: round($mde, 4),
            emergencePressure: round($epi, 4),
            archetypeTurnoverRate: round($atr, 4),
            proposalAcceptanceRatio: round($par, 4),
            governanceLatency: round($gl, 4),
            humanInterventionIndex: round($hii, 4),
            civilizationalHealthScore: round($chs, 4),
        );
    }

    /**
     * SSI = max |eigenvalue(Jacobian)|
     *
     * Approximated from state parameter rates of change.
     * Real eigenvalue computation requires full Jacobian; we use a proxy
     * based on max rate of change across state dimensions.
     */
    private function calculateSSI(WorldSnapshot $current, array $trajectory): float
    {
        if (count($trajectory) < 2) {
            return 0.0;
        }

        $maxRate = 0.0;
        $count = min(10, count($trajectory));

        for ($i = count($trajectory) - $count; $i < count($trajectory) - 1; $i++) {
            $prev = $trajectory[$i]->cosmic;
            $next = $trajectory[$i + 1]->cosmic;

            $deltaEntropy = abs($next->entropy - $prev->entropy);
            $deltaEnergy = abs($next->energy - $prev->energy);
            $deltaStrain = abs($next->strain - $prev->strain);
            $deltaStability = abs($next->stability - $prev->stability);

            // Max absolute rate across dimensions (proxy for spectral radius)
            $rate = max($deltaEntropy, $deltaEnergy, $deltaStrain, $deltaStability);
            $maxRate = max($maxRate, $rate);
        }

        // Scale: rate of 0.1/step ≈ SSI of 1.0 (instability threshold)
        return $maxRate * 10.0;
    }

    /**
     * Stability margin = distance from CTI to chaos threshold.
     */
    private function calculateStabilityMargin(WorldSnapshot $current): float
    {
        $cosmic = $current->cosmic;
        // CTI proxy: entropy * strain / stability
        $ctiProxy = ($cosmic->entropy * $cosmic->strain) / max(0.01, $cosmic->stability);
        $threshold = 0.7; // Standard chaos threshold

        return max(-1.0, min(1.0, $threshold - $ctiProxy));
    }

    /**
     * DI = 1 - Σ(influence_i²) — Herfindahl inverse.
     */
    private function calculateDiversityIndex(array $influences): float
    {
        if (empty($influences)) {
            return 0.5; // Default moderate diversity
        }

        $total = array_sum($influences);
        if ($total <= 0) return 0.0;

        $hhi = 0.0;
        foreach ($influences as $inf) {
            $share = $inf / $total;
            $hhi += $share * $share;
        }

        return max(0.0, min(1.0, 1.0 - $hhi));
    }

    /**
     * AGR = ΔAdaptation / epoch (stability trend over recent window).
     */
    private function calculateAdaptationGainRate(array $trajectory): float
    {
        if (count($trajectory) < 5) {
            return 0.0;
        }

        $window = min(20, count($trajectory));
        $start = count($trajectory) - $window;

        $firstStability = $trajectory[$start]->cosmic->stability;
        $lastStability = $trajectory[count($trajectory) - 1]->cosmic->stability;

        return ($lastStability - $firstStability) / $window;
    }

    /**
     * RES = avg(rebirth_gain / max(0.1, collapse_depth))
     */
    private function calculateRebirthEffectiveness(array $attractors): float
    {
        if (empty($attractors)) {
            return 0.0;
        }

        $totalRES = 0.0;
        $count = 0;

        foreach ($attractors as $att) {
            $rg = $att->getCumulativeRebirthGain();
            $collapses = max(1, $att->getCollapseCount());
            $instability = max(0.1, $att->getInstability());

            $totalRES += $rg / ($collapses * $instability);
            $count++;
        }

        return $count > 0 ? max(0.0, min(1.0, $totalRES / $count)) : 0.0;
    }

    /**
     * Attractor influence distribution.
     */
    private function calculateInfluences(array $attractors): array
    {
        if (empty($attractors)) {
            return [1.0]; // Single default
        }

        $influences = [];
        foreach ($attractors as $att) {
            $influences[] = $att->getPullWeight();
        }

        return $influences;
    }

    /**
     * VPS = std_dev(voting_power).
     */
    private function calculateVotingPowerSkew(array $attractors): float
    {
        if (count($attractors) < 2) {
            return 0.0;
        }

        $powers = [];
        foreach ($attractors as $att) {
            $powers[] = $att->getPullWeight();
        }

        $mean = array_sum($powers) / count($powers);
        $variance = 0.0;
        foreach ($powers as $p) {
            $variance += ($p - $mean) ** 2;
        }
        $variance /= count($powers);

        return sqrt($variance);
    }

    /**
     * API = variance(HR edges).
     */
    private function calculateAlliancePolarization(array $interactions): float
    {
        if (empty($interactions)) {
            return 0.0;
        }

        $hrScores = [];
        foreach ($interactions as $inter) {
            $hrScores[] = $inter->getHRScore();
        }

        if (empty($hrScores)) return 0.0;

        $mean = array_sum($hrScores) / count($hrScores);
        $variance = 0.0;
        foreach ($hrScores as $hr) {
            $variance += ($hr - $mean) ** 2;
        }
        $variance /= count($hrScores);

        return min(1.0, $variance * 10.0); // Scale for readability
    }

    /**
     * HBR = avg(||MemoryBias||) across all attractors.
     */
    private function calculateHistoricalBiasRatio(array $memories): float
    {
        if (empty($memories)) {
            return 0.0;
        }

        $totalBias = 0.0;
        foreach ($memories as $mem) {
            $bias = $mem->getMemoryBias();
            $magnitude = 0.0;
            foreach ($bias as $v) {
                $magnitude += $v * $v;
            }
            $totalBias += sqrt($magnitude);
        }

        return $totalBias / count($memories);
    }

    /**
     * MDE = 1.0 - (avg_inertia / max_possible_inertia).
     * High MDE = decay is working well (inertia not saturated).
     */
    private function calculateMemoryDecayEffectiveness(array $memories): float
    {
        if (empty($memories)) {
            return 1.0; // Perfect decay when no memory
        }

        $totalInertia = 0.0;
        foreach ($memories as $mem) {
            $totalInertia += $mem->getHistoricalInertia();
        }

        $avgInertia = $totalInertia / count($memories);
        // Max theoretical inertia is 1.0 (saturated)
        return max(0.0, min(1.0, 1.0 - $avgInertia));
    }

    /**
     * EPI = w1*entropy + w2*diversity + w3*GCMF
     */
    private function calculateEmergencePressure(WorldSnapshot $current, float $di, float $gcmf): float
    {
        return 0.4 * $current->cosmic->entropy
             + 0.3 * $di
             + 0.3 * $gcmf;
    }

    /**
     * CHS = composite civilizational health score [0, 1].
     */
    private function calculateCHS(float $ssi, float $di, float $agr, float $res, float $icr, float $cf): float
    {
        $chs = self::CHS_W1 * max(0.0, 1.0 - $ssi)
             + self::CHS_W2 * $di
             + self::CHS_W3 * max(0.0, min(1.0, $agr * 10.0 + 0.5)) // Normalize AGR
             + self::CHS_W4 * $res
             - self::CHS_W5 * $icr
             - self::CHS_W6 * min(1.0, $cf);

        return max(0.0, min(1.0, $chs));
    }
}
