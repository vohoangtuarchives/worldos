<?php

namespace App\Domains\World\Services;

use App\Models\World;
use App\Models\SeedTemplate;
use App\Models\WorldSeed;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;

class SeedGovernanceValidator
{
    /**
     * Seed limits per dimension (from SEED_GOVERNANCE.md)
     */
    private const DIMENSION_LIMITS = [
        'personal' => 3,
        'family' => 2,    // Regional equivalent
        'faction' => 2,   // Regional equivalent
        'city' => 2,      // Regional equivalent
        'world' => 1,     // Global
    ];

    /**
     * Check if seed can be injected into world
     */
    public function canInjectSeed(World $world, SeedTemplate $template): bool
    {
        // Rule 1: Check World Health (no spawn in CRITICAL/HALTED)
        if (!$this->checkWorldHealth($world)) {
            return false;
        }

        // Rule 2: Check Safe Mode (no spawn in SAFE_MODE)
        if (!$this->checkSafeMode($world)) {
            return false;
        }

        // Rule 3: Check dimension limits
        if (!$this->checkDimensionLimits($world, $template->dimension)) {
            return false;
        }

        return true;
    }

    /**
     * Check dimension limits
     */
    public function checkDimensionLimits(World $world, string $dimension): bool
    {
        $activeSeeds = WorldSeed::where('world_id', $world->id)
            ->where('state', \WorldOS\Blueprint\Domain\Legacy\Enums\SeedState::ACTIVE)
            ->whereHas('template', function ($q) use ($dimension) {
                $q->where('dimension', $dimension);
            })
            ->count();

        $limit = self::DIMENSION_LIMITS[strtolower($dimension)] ?? 2;

        return $activeSeeds < $limit;
    }

    /**
     * Check World Health
     */
    public function checkWorldHealth(World $world): bool
    {
        // No spawn in CRITICAL or HALTED
        return !in_array($world->health_status, [
            WorldHealthStatus::CRITICAL,
            WorldHealthStatus::HALTED,
        ], true);
    }

    /**
     * Check Safe Mode
     */
    public function checkSafeMode(World $world): bool
    {
        return $world->status !== 'SAFE_MODE';
    }

    /**
     * Get violation reason (for user feedback)
     */
    public function getViolationReason(World $world, SeedTemplate $template): ?string
    {
        if (!$this->checkWorldHealth($world)) {
            return 'World health is CRITICAL/HALTED. Cannot inject seeds.';
        }

        if (!$this->checkSafeMode($world)) {
            return 'World is in SAFE MODE. Cannot inject seeds.';
        }

        if (!$this->checkDimensionLimits($world, $template->dimension)) {
            $limit = self::DIMENSION_LIMITS[strtolower($template->dimension)] ?? 2;
            return "Dimension '{$template->dimension}' already has {$limit} active seeds (limit reached).";
        }

        return null;
    }
}
