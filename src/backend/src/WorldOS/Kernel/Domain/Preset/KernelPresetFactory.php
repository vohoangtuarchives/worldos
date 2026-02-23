<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Preset;

use WorldOS\Kernel\Domain\Policy\KernelPolicy;
use InvalidArgumentException;

/**
 * Maps UX goals (such as Explorer Mode, Survivor Mode) or default presets
 * directly to a valid KernelPolicy JSON/Array DSL structure.
 * Isolates UX from the core Engine simulation.
 */
final class KernelPresetFactory
{
    public const PRESET_STABLE_GROWTH = 'stable_growth';
    public const PRESET_Explorer = 'explorer';
    public const PRESET_SURVIVOR = 'survivor';
    public const PRESET_CHAOS = 'chaos';

    public static function createPreset(string $presetKey): KernelPolicy
    {
        return match ($presetKey) {
            self::PRESET_STABLE_GROWTH => self::buildStableGrowth(),
            self::PRESET_Explorer => self::buildExplorer(),
            self::PRESET_SURVIVOR => self::buildSurvivor(),
            self::PRESET_CHAOS => self::buildChaos(),
            default => throw new InvalidArgumentException("Unknown Kernel Preset: {$presetKey}"),
        };
    }

    private static function buildStableGrowth(): KernelPolicy
    {
        return KernelPolicy::fromArray([
            'version' => '1.0.0',
            'stability' => [
                'chaos_factor' => 0.015,
                'spectral_radius' => 0.95,
            ],
            'evolution' => [
                'mutation_strength' => 0.02,
            ],
            'fork' => [
                'max_active_branches' => 5,
            ],
            'weight' => [
                'formula' => 'clamp(w + (richness * 0.1) - (entropy_decay * 0.2), 0.0, 1.0)',
            ],
        ]);
    }

    private static function buildExplorer(): KernelPolicy
    {
        return KernelPolicy::fromArray([
            'version' => '1.0.0',
            'stability' => [
                'chaos_factor' => 0.03,
                'spectral_radius' => 0.98,
            ],
            'evolution' => [
                'mutation_strength' => 0.05,
            ],
            'fork' => [
                // Allows a highly branching multiverse graph
                'max_active_branches' => 15,
            ],
            'weight' => [
                'formula' => 'clamp(w + (anomaly * 0.3) + (richness * 0.2) - (entropy_decay * 0.1), 0.0, 1.0)',
            ],
        ]);
    }

    private static function buildSurvivor(): KernelPolicy
    {
        return KernelPolicy::fromArray([
            'version' => '1.0.0',
            'stability' => [
                'chaos_factor' => 0.01,
                'spectral_radius' => 0.92,
            ],
            'evolution' => [
                'mutation_strength' => 0.01,
            ],
            'fork' => [
                'max_active_branches' => 2,
            ],
            'weight' => [
                'formula' => 'clamp(w - (anomaly * 0.5) - (entropy_decay * 0.4), 0.0, 1.0)',
            ],
        ]);
    }

    private static function buildChaos(): KernelPolicy
    {
        return KernelPolicy::fromArray([
            'version' => '1.0.0',
            'stability' => [
                'chaos_factor' => 0.045, // Near max 0.05 limit
                'spectral_radius' => 0.99,
            ],
            'evolution' => [
                'mutation_strength' => 0.08,
            ],
            'fork' => [
                'max_active_branches' => 20,
            ],
            'weight' => [
                'formula' => 'clamp(w + (anomaly * 0.6) - (entropy_decay * 0.1), 0.0, 1.0)',
            ],
        ]);
    }
}
