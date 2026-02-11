<?php

namespace App\Domains\World\Services;

use App\Domains\World\Contracts\TickProcessor;
use App\Domains\World\Factory\PresetFactory;
use App\Domains\World\Support\DeterministicRandom;
use App\Models\World\WorldState;
use App\Models\World\WorldStateMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorldTickService
{
    /** @var TickProcessor[] */
    protected array $processors = [];

    public function __construct(
        protected PresetFactory $presetFactory,
        protected array $processorClasses = [] // Injected via config or provider
    ) {}

    public function addProcessor(TickProcessor $processor): void
    {
        $this->processors[] = $processor;
    }

    public function advance(WorldState $state): WorldState
    {
        // 1. Resolve structures
        $presetModel = $state->preset;
        $preset = $this->presetFactory->build($presetModel);
        
        $currentSnapshot = $state->snapshot;
        $currentTick = $currentSnapshot['meta']['tick'] ?? 0;
        $seed = $state->seed;

        $random = new DeterministicRandom(
            seed: $seed,
            tick: $currentTick
        );

        // 2. Clone snapshot (array copy in PHP is copy-on-write, effectively cloning for mutation)
        $nextSnapshot = $currentSnapshot;

        // 3. Run processors
        foreach ($this->processors as $processor) {
            $nextSnapshot = $processor->process($nextSnapshot, $preset, $random);
        }

        // 4. Update meta
        $nextSnapshot['meta']['tick'] = $currentTick + 1;
        
        // 5. Persist new state
        return DB::transaction(function () use ($state, $nextSnapshot, $seed) {
           return WorldState::create([
               'preset_id' => $state->preset_id,
               'version' => $state->version,
               'seed' => $seed,
               'snapshot' => $nextSnapshot,
               'parent_state_id' => $state->id,
               'created_at' => now(),
           ]);
        });
    }
}
