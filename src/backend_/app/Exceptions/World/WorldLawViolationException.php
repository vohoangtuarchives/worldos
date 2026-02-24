<?php

namespace App\Exceptions\World;

use App\Exceptions\WorldOSException;

class WorldLawViolationException extends WorldOSException
{
    public const MAGIC_VIOLATION = 'WORLD_MAGIC_VIOLATION';
    public const POWER_CEILING_EXCEEDED = 'WORLD_POWER_CEILING_EXCEEDED';
    public const CULTIVATION_FORBIDDEN = 'WORLD_CULTIVATION_FORBIDDEN';
    public const TECH_LEVEL_VIOLATION = 'WORLD_TECH_LEVEL_VIOLATION';
    public const MYTH_EMERGENCE_BLOCKED = 'WORLD_MYTH_EMERGENCE_BLOCKED';
    public const TEMPORAL_PARADOX = 'WORLD_TEMPORAL_PARADOX';

    public static function magicViolation(string $action, array $context = []): self
    {
        return new self(
            "Magic action '{$action}' violates world laws",
            self::MAGIC_VIOLATION,
            array_merge(['action' => $action], $context)
        );
    }

    public static function powerCeilingExceeded(int $attempted, int $maximum, array $context = []): self
    {
        return new self(
            "Power level {$attempted} exceeds world ceiling of {$maximum}",
            self::POWER_CEILING_EXCEEDED,
            array_merge(['attempted' => $attempted, 'maximum' => $maximum], $context)
        );
    }

    public static function cultivationForbidden(string $action, array $context = []): self
    {
        return new self(
            "Cultivation action '{$action}' is forbidden in this world",
            self::CULTIVATION_FORBIDDEN,
            array_merge(['action' => $action], $context)
        );
    }

    public static function techLevelViolation(string $technology, string $requiredLevel, string $currentLevel, array $context = []): self
    {
        return new self(
            "Technology '{$technology}' requires {$requiredLevel} but world is {$currentLevel}",
            self::TECH_LEVEL_VIOLATION,
            array_merge(['technology' => $technology, 'required' => $requiredLevel, 'current' => $currentLevel], $context)
        );
    }

    public static function mythEmergenceBlocked(string $myth, float $beliefStrength, float $requiredThreshold, array $context = []): self
    {
        return new self(
            "Myth '{$myth}' emergence blocked: belief strength {$beliefStrength} below threshold {$requiredThreshold}",
            self::MYTH_EMERGENCE_BLOCKED,
            array_merge(['myth' => $myth, 'belief_strength' => $beliefStrength, 'required_threshold' => $requiredThreshold], $context)
        );
    }

    public static function temporalParadox(string $action, string $reason, array $context = []): self
    {
        return new self(
            "Temporal paradox detected for action '{$action}': {$reason}",
            self::TEMPORAL_PARADOX,
            array_merge(['action' => $action, 'reason' => $reason], $context)
        );
    }
}
