<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

final readonly class AlertRule
{
    public function __construct(
        public string $code,
        public string $domain,
        public string $severity,
        public string $metricSource,
        public string $operator,
        public float $threshold,
        public int $cooldownEpochs,
        public ?string $autoAction,
        public string $description,
    ) {
    }

    public function evaluate(float $metricValue): bool
    {
        return match ($this->operator) {
            '>' => $metricValue > $this->threshold,
            '<' => $metricValue < $this->threshold,
            '>=' => $metricValue >= $this->threshold,
            '<=' => $metricValue <= $this->threshold,
            default => false,
        };
    }

    public static function catalog(): array
    {
        return [
            new self('SSI_CRITICAL', 'STABILITY', 'CRITICAL', 'ssi', '>', 1.0, 3,
                'FREEZE_SIMULATION', 'Spectral Stability Index exceeds 1.0 — system unstable'),
            new self('SSI_WARNING', 'STABILITY', 'WARNING', 'ssi', '>', 0.9, 5,
                null, 'SSI approaching instability threshold'),
            new self('STABILITY_DRIFT', 'STABILITY', 'HIGH', 'stability_margin', '<', 0.1, 10,
                null, 'Stability margin critically low — crisis accumulating'),
            new self('COLLAPSE_IMMINENT', 'CHAOS', 'HIGH', 'collapse_probability', '>', 0.7, 5,
                null, 'Collapse probability > 70% in next 10 epochs'),
            new self('HIGH_COLLAPSE_FREQUENCY', 'CHAOS', 'HIGH', 'cf', '>', 0.06, 10,
                null, 'Collapse frequency > 3 per 50 epochs'),
            new self('CHAOS_CLUSTER', 'CHAOS', 'CRITICAL', 'chaos_cluster', '>', 0.0, 5,
                null, '3+ collapses within 10 epochs — systemic instability'),
            new self('MONOPOLY_RISK', 'POWER', 'HIGH', 'icr', '>', 0.4, 10,
                null, 'Single attractor holds >40% influence'),
            new self('VOTING_DOMINANCE', 'POWER', 'CRITICAL', 'max_voting_power', '>', 0.15, 5,
                'REJECT_PROPOSAL', 'Voting power >15% — auto-reject proposals'),
            new self('POLARIZATION_HIGH', 'POWER', 'WARNING', 'api', '>', 0.5, 15,
                null, 'Alliance polarization dangerously high'),
            new self('HISTORICAL_LOCK', 'MEMORY', 'HIGH', 'hbr', '>', 0.25, 10,
                null, 'System destiny-locked — historical bias > 0.25'),
            new self('GCMF_OVERBIAS', 'MEMORY', 'WARNING', 'cm', '>', 0.25, 10,
                null, 'Collective mood dominating physics'),
            new self('MEMORY_STAGNATION', 'MEMORY', 'WARNING', 'mde', '<', 0.3, 20,
                null, 'Memory decay ineffective — system stuck in past'),
            new self('PROPOSAL_SPAM', 'GOVERNANCE', 'WARNING', 'proposal_rate', '>', 0.25, 20,
                null, 'AI overactive — >5 proposals per 20 epochs'),
            new self('LOW_ACCEPTANCE_RATIO', 'GOVERNANCE', 'WARNING', 'par', '<', 0.1, 30,
                null, 'AI proposal quality very low'),
            new self('AUTO_APPROVAL_PATTERN', 'GOVERNANCE', 'WARNING', 'par', '>', 0.9, 30,
                null, 'Approval rate too high — possible loss of oversight'),
        ];
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'domain' => $this->domain,
            'severity' => $this->severity,
            'metric_source' => $this->metricSource,
            'operator' => $this->operator,
            'threshold' => $this->threshold,
            'cooldown_epochs' => $this->cooldownEpochs,
            'auto_action' => $this->autoAction,
            'description' => $this->description,
        ];
    }
}
