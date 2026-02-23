<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

/**
 * UniverseStyleVersion — immutable versioned style configuration.
 * Once published, a style version is FROZEN. Changes create new versions.
 */
final readonly class UniverseStyleVersion
{
    public function __construct(
        public string $styleId,
        public int $versionNumber,
        public array $weightProfile,
        public array $alignmentProfile,
        public array $arcProfile,
        public string $checksumHash,
    ) {
    }

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

    public function newVersion(array $newWeight, array $newAlignment, array $newArc): self
    {
        $w = array_merge($this->weightProfile, $newWeight);
        $a = array_merge($this->alignmentProfile, $newAlignment);
        $arc = array_merge($this->arcProfile, $newArc);
        return new self(
            styleId: $this->styleId,
            versionNumber: $this->versionNumber + 1,
            weightProfile: $w,
            alignmentProfile: $a,
            arcProfile: $arc,
            checksumHash: self::computeChecksum($w, $a, $arc),
        );
    }

    public function styleBias(string $currentArchetype): array
    {
        $archetypeAlignment = $this->alignmentProfile[$currentArchetype] ?? 0.0;
        $orderBias = $this->weightProfile['order_bias'] ?? 0.5;
        $chaosSens = $this->weightProfile['chaos_sensitivity'] ?? 0.3;
        $scale = 0.05;
        return [
            'entropy' => -$scale * $orderBias * $archetypeAlignment,
            'energy' => $scale * $archetypeAlignment * 0.5,
            'stability' => $scale * $orderBias * 0.3,
            'strain' => -$scale * (1.0 - $chaosSens) * 0.3,
        ];
    }

    private static function computeChecksum(array $w, array $a, array $arc): string
    {
        return hash('sha256', json_encode(['w' => $w, 'a' => $a, 'arc' => $arc]));
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
