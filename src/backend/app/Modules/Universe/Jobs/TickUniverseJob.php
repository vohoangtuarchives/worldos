<?php

declare(strict_types=1);

namespace App\Modules\Universe\Jobs;

use App\Modules\Universe\Actions\TickUniverseAction;
use App\Modules\Universe\Dto\TickUniverseDTO;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\ValueObjects\UniverseId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TickUniverseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public readonly string $universeId,
    ) {
    }

    public function handle(
        TickUniverseAction $tickAction,
        UniverseRepositoryInterface $universeRepo
    ): void {
        Log::info("Processing TickUniverseJob for Universe [{$this->universeId}]");

        $dto = new TickUniverseDTO(universeId: $this->universeId);
        $result = $tickAction->handle($dto);

        if ($result['status'] === 'success') {
            Log::info("Successfully ticked Universe [{$this->universeId}]. Checking for next tick...");
            
            // Check if there are more ticks required to meet target_tick
            $universe = $universeRepo->findById(UniverseId::fromString($this->universeId));
            $targetTick = $universe?->getTargetTick();
            $currentTick = $universe?->getCurrentTick();
            
            if ($universe !== null && $targetTick !== null && $currentTick < $targetTick) {
                // Auto-queue the next tick in the background to avoid monopolizing workers
                self::dispatch($this->universeId);
            } elseif ($universe !== null && $currentTick >= $targetTick) {
                Log::info("Universe [{$this->universeId}] has reached target tick {$targetTick}. Stopping async loop.");
            }
        } else {
            Log::warning("Universe [{$this->universeId}] rejected tick: " . ($result['error'] ?? 'Unknown Error'));
        }
    }
}
