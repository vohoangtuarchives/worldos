<?php

namespace App\Domains\Cosmology\Services;

class ExperienceMode
{
    public const MODE_OBSERVER = 'OBSERVER';
    public const MODE_LEGENDARY = 'LEGENDARY';
    public const MODE_ARCHITECT = 'ARCHITECT';

    public static function getPermissions(string $mode): array
    {
        return match ($mode) {
            self::MODE_OBSERVER => [
                'view_timeline',
                'view_analytics',
            ],
            self::MODE_LEGENDARY => [
                'view_timeline',
                'view_analytics',
                'defy_fate', // Can burn Willpower
                'influence_faction',
            ],
            self::MODE_ARCHITECT => [
                'view_timeline',
                'view_analytics',
                'adjust_parameters', // Can tweak BasePhysicsEngine constants
                'reset_universe',
                'inject_event',
            ],
            default => ['view_timeline'],
        };
    }
}
