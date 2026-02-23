<?php

namespace App\Jobs;

use WorldOS\Legacy\Application\Evolution\Services\EvolutionKernel;
use App\Models\World;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvolveWorldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $world;

    /**
     * Create a new job instance.
     */
    public function __construct(World $world)
    {
        $this->world = $world;
    }

    /**
     * Execute the job.
     */
    public function handle(EvolutionKernel $kernel): void
    {
        try {
            // Eager load state and profile if not set
            if (!$this->world->relationLoaded('state')) {
                $this->world->load('state.evolutionProfile');
            }

            $state = $this->world->state;
            
            if (!$state || !$state->evolutionProfile) {
                Log::warning("World {$this->world->id} cannot tick: Missing state or profile.");
                return;
            }

            // 1. Convert current state to Value Object
            // The kernel expects a ValueObject, we need to map the array properly
            // Assuming the Kernel handles array-to-VO conversion internally or expects VO
            // Let's create the VO here for clarity
            $currentVectorVO = \WorldOS\Evolution\Domain\Legacy\ValueObjects\StateVector::fromArray($state->state_vector ?? []);

            // 2. Calculate next state
            $nextVectorVO = $kernel->nextTick($currentVectorVO, $state->evolutionProfile);
            
            // 3. Update DB
            $state->state_vector = $nextVectorVO->toArray();
            $state->save();

            // 4. Increment Tick
            $this->world->increment('current_tick');
            
            // Optional: Log tick for observability
            // Log::info("World {$this->world->name} ticked to {$this->world->current_tick}");

        } catch (\Exception $e) {
            Log::error("Tick failed for World {$this->world->id}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
