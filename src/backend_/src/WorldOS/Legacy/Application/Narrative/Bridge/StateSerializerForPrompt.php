<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Bridge;

/**
 * Serialize narrative_driven_state for inclusion in chapter prompt.
 */
final class StateSerializerForPrompt
{
    public static function serialize(?array $state): string
    {
        if ($state === null || $state === []) {
            return 'Current world state (narrative): shadow_presence=0, magic_stability=1, threat_level=0';
        }
        $shadow = round($state['shadow_presence'] ?? 0, 2);
        $magic = round($state['magic_stability'] ?? 1, 2);
        $threat = round($state['threat_level'] ?? 0, 2);
        return "Current world state (narrative): shadow_presence={$shadow}, magic_stability={$magic}, threat_level={$threat}";
    }
}
