<?php

namespace Tuzy\Domain\Time;

use App\Models\World;

/**
 * TimeManager
 * 
 * Handles the flow of time in the simulation.
 * Converts between float time (50.5) and human-readable dates (Year 50, Month 6).
 * Determines deltaTime based on world tension.
 */
class TimeManager
{
    /**
     * Constants for time conversion
     * Base unit: 1.0 = 1 Solar Year
     */
    public const YEAR = 1.0;
    public const MONTH = 1.0 / 12.0;         // ~0.0833
    public const DAY = 1.0 / 365.25;         // ~0.0027

    /**
     * Get the appropriate delta time for the current world state.
     * 
     * @param World $world
     * @param float $tensionLevel (0.0 - 1.0) High tension = slower time
     * @return float deltaTime
     */
    public function determineDeltaTime(World $world, float $tensionLevel): float
    {
        // Base speed: 1 Year per tick
        $baseDelta = self::YEAR;

        // Crisis Mode: If tension > 0.7, slow down to Days/Weeks
        if ($tensionLevel > 0.9) {
            return self::DAY; // 1 Day per tick (Extreme crisis)
        }
        
        if ($tensionLevel > 0.7) {
            return self::MONTH; // 1 Month per tick (High tension)
        }

        if ($tensionLevel > 0.4) {
             return self::MONTH * 3; // Quarter year per tick (Moderate tension)
        }

        return $baseDelta;
    }

    /**
     * Format float time into a string based on world's calendar system.
     * 
     * @param float $time
     * @param array|null $calendarConfig
     * @return string
     */
    public function formatTime(float $time, ?array $calendarConfig = null): string
    {
        $year = floor($time);
        $fraction = $time - $year;
        
        $month = floor($fraction * 12) + 1;
        $day = floor(($fraction * 12 - floor($fraction * 12)) * 30) + 1;

        $era = $calendarConfig['era'] ?? 'Niên đại Khởi nguyên';

        return "Năm {$year}, Tháng {$month}, Ngày {$day} ({$era})";
    }

    /**
     * Advance world time
     */
    public function advance(World $world, float $deltaTime): void
    {
        $world->current_time += $deltaTime;
        $world->tick++; // Ticks still count total steps taken
        // $world->save(); // Caller should save to minimize DB writes
    }
}
