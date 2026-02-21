<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Anchors\StructuralAnchorRegistry;
use App\Domains\Cosmology\Contracts\StructuralAnchorInterface;
use Tuzy\Domain\Cosmology\ValueObject\ConstraintProfile;
use App\Domains\Saga\Services\GenesisPresetService;

/**
 * Maps preset_key → (Structural Anchor + ConstraintProfile).
 * Single source for Khai Thiên and Civilization Forge.
 */
final class ForgePresetMapper
{
    public function __construct(
        private readonly GenesisPresetService $genesisPresets
    ) {}

    /**
     * Resolve preset key to anchor + intent (for ConstraintProfile).
     * Returns null if preset not found or not mappable.
     *
     * @return array{anchor: StructuralAnchorInterface, profile: ConstraintProfile}|null
     */
    public function resolve(string $presetKey): ?array
    {
        $preset = $this->genesisPresets->find($presetKey);
        if ($preset === null) {
            return null;
        }

        $anchorKey = $this->mapPresetToAnchor($presetKey, $preset);
        $anchor = StructuralAnchorRegistry::get($anchorKey);
        if ($anchor === null) {
            $anchor = StructuralAnchorRegistry::get('academic_system');
        }

        $intent = $this->presetToIntent($preset);
        $profile = ConstraintProfile::fromIntent($intent);

        return ['anchor' => $anchor, 'profile' => $profile];
    }

    private function mapPresetToAnchor(string $presetKey, array $preset): string
    {
        $genre = $preset['genre'] ?? '';
        $social = $preset['social_structure'] ?? '';
        if (str_contains($presetKey, 'cung_dau') || str_contains($presetKey, 'dai_tuong') || $social === 'EMPIRE') {
            return 'faction_system';
        }
        if (str_contains($presetKey, 'hoc_vien') || str_contains($presetKey, 'huyen_mon') || str_contains($presetKey, 'ma_dao')) {
            return 'academic_system';
        }
        if (str_contains($presetKey, 'do_thi') || str_contains($presetKey, 'thuong') || $genre === 'urban') {
            return 'commercial_system';
        }
        return 'academic_system';
    }

    private function presetToIntent(array $preset): array
    {
        $powerCeiling = $preset['power_ceiling'] ?? 'HUMAN';
        $crisis = $preset['starting_crisis'] ?? 'NONE';
        return [
            'narrative_density' => 'medium',
            'power_gradient' => $powerCeiling === 'IMMORTAL' ? 'steep' : ($powerCeiling === 'HUMAN' ? 'gentle' : 'medium'),
            'resource_density' => 'medium',
            'perception_complexity' => 'medium',
            'conflict_intensity' => $crisis !== 'NONE' ? 'high' : 'medium',
            'social_thickness' => 'medium',
            'mythology_layer' => 'subtle',
        ];
    }
}
