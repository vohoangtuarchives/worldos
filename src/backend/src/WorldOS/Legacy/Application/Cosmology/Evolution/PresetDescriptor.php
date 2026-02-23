<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Evolution;

/**
 * Data-only descriptor for evolution dynamics. No closures; kernel reads these values.
 * Maps to WorldStateVector dimensions and current BasePhysicsEngine coefficients.
 *
 * @phpstan-type PresetArray array{
 *   key: string,
 *   entropy_inequality_rate?: float,
 *   entropy_trauma_rate?: float,
 *   entropy_stagnation_rate?: float,
 *   entropy_dampening?: float,
 *   order_cohesion_rate?: float,
 *   order_military_rate?: float,
 *   order_entropy_decay?: float,
 *   inequality_accumulation?: float,
 *   inequality_elite_graft?: float,
 *   inequality_redistribution?: float,
 *   trauma_war_rate?: float,
 *   trauma_collapse_rate?: float,
 *   trauma_healing_rate?: float,
 *   resource_consumption?: float,
 *   resource_innovation_yield?: float,
 *   resource_war_cost?: float,
 *   critical_entropy_threshold?: float,
 *   critical_inequality_threshold?: float,
 *   tipping_point_multiplier?: float,
 *   contradiction_collapse_threshold?: float,
 *   innovation_min_for_survival?: float,
 *   resource_flow_collapse_threshold?: float,
 *   instability_sensitivity?: float,
 *   mutation_bias?: array<string, float>
 * }
 */
final class PresetDescriptor
{
    public const KEY_DEFAULT = 'cosmology_legacy';

    public function __construct(
        public readonly string $key,
        public readonly array $params
    ) {
    }

    public function get(string $param, float $default = 0.0): float
    {
        return (float) ($this->params[$param] ?? $default);
    }

    /** @return array<string, float> */
    public function getMutationBias(): array
    {
        $bias = $this->params['mutation_bias'] ?? [];
        return is_array($bias) ? $bias : [];
    }

    /**
     * Build preset from World config; merges default with config.mutation_bias (Phase 4 blueprint).
     */
    public static function fromWorld(\App\Models\World $world): self
    {
        $default = self::default();
        $params = array_merge([], $default->params);
        $config = is_array($world->config) ? $world->config : [];
        $bias = $config['mutation_bias'] ?? [];
        if (!is_array($bias)) {
            return $default;
        }
        $params['mutation_bias'] = $bias;
        if (isset($bias['order_bias'])) {
            $params['order_cohesion_rate'] = ($params['order_cohesion_rate'] ?? 0.04) + (float) $bias['order_bias'];
        }
        if (isset($bias['redistribution_bias'])) {
            $params['inequality_redistribution'] = ($params['inequality_redistribution'] ?? 0.03) + (float) $bias['redistribution_bias'];
        }
        if (isset($bias['stability_bias'])) {
            $params['entropy_dampening'] = ($params['entropy_dampening'] ?? 0.04) + (float) $bias['stability_bias'];
        }
        if (isset($bias['resilience_bias'])) {
            $params['resource_innovation_yield'] = ($params['resource_innovation_yield'] ?? 0.02) + (float) $bias['resilience_bias'];
        }
        return new self(self::KEY_DEFAULT, $params);
    }

    /**
     * Default preset matching current BasePhysicsEngine hardcoded coefficients.
     */
    public static function default(): self
    {
        return new self(self::KEY_DEFAULT, [
            'entropy_inequality_rate' => 0.05,
            'entropy_trauma_rate' => 0.03,
            'entropy_stagnation_rate' => 0.02,
            'entropy_dampening' => 0.04,
            'order_cohesion_rate' => 0.04,
            'order_military_rate' => 0.01,
            'order_entropy_decay' => 0.05,
            'inequality_accumulation' => 0.01,
            'inequality_elite_graft' => 0.02,
            'inequality_redistribution' => 0.03,
            'trauma_war_rate' => 0.05,
            'trauma_collapse_rate' => 0.10,
            'trauma_healing_rate' => 0.005,
            'resource_consumption' => 0.01,
            'resource_innovation_yield' => 0.02,
            'resource_war_cost' => 0.05,
            'critical_entropy_threshold' => 0.85,
            'critical_inequality_threshold' => 0.70,
            'tipping_point_multiplier' => 2.5,
            'contradiction_collapse_threshold' => 0.70,
            'innovation_min_for_survival' => 0.15,
            'resource_flow_collapse_threshold' => 0.05,
            'instability_sensitivity' => 0.04,
            'mutation_bias' => [],
        ]);
    }
}
