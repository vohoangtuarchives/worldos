<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * MetricsSnapshot â€” immutable snapshot of all 18 KPIs.
 *
 * 6 groups:
 *   Stability:  SSI, CF, StabilityMargin
 *   Evolution:  DI, AGR, RES
 *   Power:      ICR, VPS, API
 *   Memory:     HBR, CM, MDE
 *   Emergence:  EPI, ATR
 *   Governance: PAR, GL, HII
 *   Meta:       CHS (composite)
 */
final class MetricsSnapshot
{
    public function __construct(
        public readonly int $epoch,

        // Stability KPIs
        public readonly float $spectralStabilityIndex,   // SSI: max |eigen(J)|, lower = safer
        public readonly float $collapseFrequency,        // CF: collapses / 50 epochs
        public readonly float $stabilityMargin,          // CTI_current - chaos_threshold

        // Evolution KPIs
        public readonly float $diversityIndex,           // DI: 1 - Î£(influence_iÂ²), Herfindahl inverse
        public readonly float $adaptationGainRate,       // AGR: Î”Adaptation / epoch
        public readonly float $rebirthEffectiveness,     // RES: avg(rebirth_gain / collapse_depth)

        // Power KPIs
        public readonly float $influenceConcentration,   // ICR: max(attractor_influence)
        public readonly float $votingPowerSkew,          // VPS: std_dev(voting_power)
        public readonly float $alliancePolarization,     // API: variance(HR edges)

        // Memory KPIs
        public readonly float $historicalBiasRatio,      // HBR: ||MemoryBias|| / total_force
        public readonly float $collectiveMomentum,       // CM: |GCMF|
        public readonly float $memoryDecayEffectiveness, // MDE: decay rate impact score

        // Emergence KPIs
        public readonly float $emergencePressure,        // EPI: w1*entropy + w2*diversity + w3*GCMF
        public readonly float $archetypeTurnoverRate,    // ATR: new_archetypes / 100 epochs

        // Governance KPIs
        public readonly float $proposalAcceptanceRatio,  // PAR: approved / proposed
        public readonly float $governanceLatency,        // GL: avg epochs to approval
        public readonly float $humanInterventionIndex,   // HII: manual_override / 50 epochs

        // Meta
        public readonly float $civilizationalHealthScore, // CHS: composite
    ) {}

    /**
     * The 4 critical KPIs that must always be visible.
     */
    public function criticalFour(): array
    {
        return [
            'SSI' => $this->spectralStabilityIndex,
            'DI' => $this->diversityIndex,
            'CF' => $this->collapseFrequency,
            'HBR' => $this->historicalBiasRatio,
        ];
    }

    /**
     * Severity level derived from KPIs.
     */
    public function overallSeverity(): string
    {
        if ($this->spectralStabilityIndex > 1.0 || $this->collapseFrequency > 3.0) {
            return 'CRITICAL';
        }
        if ($this->spectralStabilityIndex > 0.8 || $this->diversityIndex < 0.3) {
            return 'WARNING';
        }
        return 'HEALTHY';
    }

    public function toArray(): array
    {
        return [
            'epoch' => $this->epoch,
            'stability' => [
                'ssi' => round($this->spectralStabilityIndex, 4),
                'cf' => round($this->collapseFrequency, 4),
                'stability_margin' => round($this->stabilityMargin, 4),
            ],
            'evolution' => [
                'di' => round($this->diversityIndex, 4),
                'agr' => round($this->adaptationGainRate, 4),
                'res' => round($this->rebirthEffectiveness, 4),
            ],
            'power' => [
                'icr' => round($this->influenceConcentration, 4),
                'vps' => round($this->votingPowerSkew, 4),
                'api' => round($this->alliancePolarization, 4),
            ],
            'memory' => [
                'hbr' => round($this->historicalBiasRatio, 4),
                'cm' => round($this->collectiveMomentum, 4),
                'mde' => round($this->memoryDecayEffectiveness, 4),
            ],
            'emergence' => [
                'epi' => round($this->emergencePressure, 4),
                'atr' => round($this->archetypeTurnoverRate, 4),
            ],
            'governance' => [
                'par' => round($this->proposalAcceptanceRatio, 4),
                'gl' => round($this->governanceLatency, 4),
                'hii' => round($this->humanInterventionIndex, 4),
            ],
            'meta' => [
                'chs' => round($this->civilizationalHealthScore, 4),
            ],
            'severity' => $this->overallSeverity(),
            'critical_four' => $this->criticalFour(),
        ];
    }

    public static function fromArray(array $d): self
    {
        $s = $d['stability'] ?? [];
        $e = $d['evolution'] ?? [];
        $p = $d['power'] ?? [];
        $m = $d['memory'] ?? [];
        $em = $d['emergence'] ?? [];
        $g = $d['governance'] ?? [];

        return new self(
            epoch: $d['epoch'] ?? 0,
            spectralStabilityIndex: (float)($s['ssi'] ?? 0),
            collapseFrequency: (float)($s['cf'] ?? 0),
            stabilityMargin: (float)($s['stability_margin'] ?? 0),
            diversityIndex: (float)($e['di'] ?? 0),
            adaptationGainRate: (float)($e['agr'] ?? 0),
            rebirthEffectiveness: (float)($e['res'] ?? 0),
            influenceConcentration: (float)($p['icr'] ?? 0),
            votingPowerSkew: (float)($p['vps'] ?? 0),
            alliancePolarization: (float)($p['api'] ?? 0),
            historicalBiasRatio: (float)($m['hbr'] ?? 0),
            collectiveMomentum: (float)($m['cm'] ?? 0),
            memoryDecayEffectiveness: (float)($m['mde'] ?? 0),
            emergencePressure: (float)($em['epi'] ?? 0),
            archetypeTurnoverRate: (float)($em['atr'] ?? 0),
            proposalAcceptanceRatio: (float)($g['par'] ?? 0),
            governanceLatency: (float)($g['gl'] ?? 0),
            humanInterventionIndex: (float)($g['hii'] ?? 0),
            civilizationalHealthScore: (float)(($d['meta'] ?? [])['chs'] ?? 0),
        );
    }
}


