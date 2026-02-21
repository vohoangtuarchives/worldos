<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\ValueObjects;

/**
 * UniverseStyleVersion â€” immutable versioned style configuration.
 *
 * From RFC Â§6.1:
 *   - weight_profile: biases for order, diversity, chaos sensitivity, emergence
 *   - alignment_profile: per-archetype alignment scores
 *   - arc_profile: preferred narrative arc shape
 *
 * Once published, a style version is FROZEN. Changes create new versions.
 * Checksum = sha256(weight + alignment + arc) for reproducibility.
 */
final class UniverseStyleVersion
{
    public function __construct(
        public readonly string $styleId,
        public readonly int $versionNumber,
        public readonly array $weightProfile,
        public readonly array $alignmentProfile,
        public readonly array $arcProfile,
        public readonly string $checksumHash,
    ) {}

    /**
     * Default style: "Transcendent Order" â€” favors long eras of order
     * with occasional controlled chaos.
     */
    public static function defaultStyle(): self
    {
        $weight = [
            'order_bias' => 0.8,
            'diversity_bias' => 0.4,
            'chaos_sensitivity' => 0.3,
            'emergence_threshold' => 0.7,
        ];

        $alignment = [
            'celestial_harmony' => 0.8,
            'transcendent_order' => 0.7,
            'golden_age' => 0.6,
            'martial_world' => 0.3,
            'rebirth_dawn' => 0.4,
            'demon_path' => -0.2,
            'apocalypse' => -0.4,
            'void_silence' => -0.3,
        ];

        $arc = [
            'preferred_shape' => ['long_emergence', 'prolonged_dominance', 'slow_decline'],
            'min_dominance_duration' => 'high',
            'max_chaos_burst_length' => 'low',
        ];

        return new self(
            styleId: 'transcendent_order',
            versionNumber: 1,
            weightProfile: $weight,
            alignmentProfile: $alignment,
            arcProfile: $arc,
            checksumHash: self::computeChecksum($weight, $alignment, $arc),
        );
    }

    /**
     * Create a new version with modified profiles.
     */
    public function newVersion(array $newWeight, array $newAlignment, array $newArc): self
    {
        return new self(
            styleId: $this->styleId,
            versionNumber: $this->versionNumber + 1,
            weightProfile: array_merge($this->weightProfile, $newWeight),
            alignmentProfile: array_merge($this->alignmentProfile, $newAlignment),
            arcProfile: array_merge($this->arcProfile, $newArc),
            checksumHash: self::computeChecksum(
                array_merge($this->weightProfile, $newWeight),
                array_merge($this->alignmentProfile, $newAlignment),
                array_merge($this->arcProfile, $newArc),
            ),
        );
    }

    /**
     * Style bias vector for physics.
     * Converts alignment + weight into a bias applied to evolution equations.
     */
    public function styleBias(string $currentArchetype): array
    {
        $archetypeAlignment = $this->alignmentProfile[$currentArchetype] ?? 0.0;
        $orderBias = $this->weightProfile['order_bias'] ?? 0.5;
        $chaosSens = $this->weightProfile['chaos_sensitivity'] ?? 0.3;

        // Scale factor: how strongly this style pushes
        $scale = 0.05; // Small â€” style is subtle bias, not override

        return [
            'entropy' => -$scale * $orderBias * $archetypeAlignment,
            'energy' => $scale * $archetypeAlignment * 0.5,
            'stability' => $scale * $orderBias * 0.3,
            'strain' => -$scale * (1.0 - $chaosSens) * 0.3,
        ];
    }

    private static function computeChecksum(array $w, array $a, array $arc): string
    {
        $payload = json_encode(['w' => $w, 'a' => $a, 'arc' => $arc]);
        return hash('sha256', $payload);
    }

    public function toArray(): array
    {
        return [
            'style_id' => $this->styleId,
            'version_number' => $this->versionNumber,
            'weight_profile' => $this->weightProfile,
            'alignment_profile' => $this->alignmentProfile,
            'arc_profile' => $this->arcProfile,
            'checksum_hash' => $this->checksumHash,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            styleId: $data['style_id'],
            versionNumber: $data['version_number'] ?? 1,
            weightProfile: $data['weight_profile'] ?? [],
            alignmentProfile: $data['alignment_profile'] ?? [],
            arcProfile: $data['arc_profile'] ?? [],
            checksumHash: $data['checksum_hash'] ?? '',
        );
    }
}


