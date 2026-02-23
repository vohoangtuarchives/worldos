<?php

namespace App\Jobs;

use App\Application\World\Actions\TickWorldAction;
use WorldOS\Legacy\Infrastructure\Character\Repositories\CharacterSurvivalRepository;
use WorldOS\Legacy\Infrastructure\World\Repositories\WorldRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TickWorldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $worldId;
    public $tick;

    /**
     * Create a new job instance.
     */
    public function __construct($worldId, $tick)
    {
        $this->worldId = $worldId;
        $this->tick = $tick;
    }

    /**
     * Execute the job.
     */
    public function handle(
        TickWorldAction $tickAction,
        WorldRepository $worldRepository,
        CharacterSurvivalRepository $characterRepository
    ): void
    {
        try {
            $world = $worldRepository->findById($this->worldId);
            
            if (!$world) {
                Log::error("TickWorldJob: World {$this->worldId} not found.");
                return;
            }

            $characters = $characterRepository->findByWorldId($this->worldId);
            
            // Execute the character simulation tick
            $tickAction->execute($world, collect($characters));
            
        } catch (\Exception $e) {
            Log::error("TickWorldJob failed for World {$this->worldId}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
