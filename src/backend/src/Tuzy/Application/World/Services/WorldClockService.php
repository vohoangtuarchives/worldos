<?php

namespace Tuzy\Application\World\Services;

use App\Models\World;
use Illuminate\Support\Facades\DB;

class WorldClockService
{
    /**
     * Advance the World Clock by one or more ticks.
     * Uses a database lock to ensure atomicity.
     */
    public function tick(World $world, int $step = 1): int
    {
        return DB::transaction(function () use ($world, $step) {
            $clock = $world->clock()->lockForUpdate()->first();

            if (! $clock) {
                throw new \RuntimeException('WorldClock not found for World #' . $world->id);
            }

            $clock->increment('current_tick', $step);
            $clock->refresh();

            return $clock->current_tick;
        });
    }
}
