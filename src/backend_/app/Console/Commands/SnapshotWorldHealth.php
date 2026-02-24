<?php

namespace App\Console\Commands;

use App\Models\World;
use App\Models\WorldHealthSnapshot;
use Illuminate\Console\Command;

class SnapshotWorldHealth extends Command
{
    protected $signature = 'world:snapshot-health';
    protected $description = 'Take a snapshot of all worlds\' current health status';

    public function handle()
    {
        $worlds = World::all();
        $count = 0;

        foreach ($worlds as $world) {
            // Skip if halted (no need to keep logging dead worlds)
            if ($world->health_status === \WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus::HALTED) {
                continue;
            }

            WorldHealthSnapshot::create([
                'world_id' => $world->id,
                'health_status' => $world->health_status,
                'health_score' => null, // TODO: Implement numeric score if needed
                'tick' => $world->clock->current_tick ?? null,
                'metadata' => [
                    'world_name' => $world->name,
                ],
                'recorded_at' => now(),
            ]);

            $count++;
        }

        $this->info("Recorded health snapshots for {$count} worlds.");
        return Command::SUCCESS;
    }
}
