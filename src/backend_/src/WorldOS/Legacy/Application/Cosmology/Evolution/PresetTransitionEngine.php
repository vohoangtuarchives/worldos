<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Evolution;

/**
 * Maps ArcPhase to preset key. PresetRegistry resolves key to PresetDescriptor.
 */
final class PresetTransitionEngine
{
    /** @var array<string, string> ArcPhase value => preset key */
    private array $map;

    public function __construct(
        private readonly PresetRegistry $registry,
        ?array $map = null
    ) {
        $this->map = $map ?? [
            ArcPhase::GENESIS->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::EXPANSION->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::GOLDEN_AGE->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::STAGNATION->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::CRISIS->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::COLLAPSE->value => PresetDescriptor::KEY_DEFAULT,
            ArcPhase::REFORMATION->value => PresetDescriptor::KEY_DEFAULT,
        ];
    }

    public function resolve(ArcPhase $phase): PresetDescriptor
    {
        $key = $this->map[$phase->value] ?? PresetDescriptor::KEY_DEFAULT;
        return $this->registry->get($key);
    }
}
