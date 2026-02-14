<?php

namespace App\Domains\Saga\Actions;

use App\Models\World;
use App\Models\GateChannel;
use App\Domains\Saga\Services\EntropyPressureService;
use App\Domains\Saga\Services\PhysicsMutator;
use App\Domains\World\Services\WorldEventLedger;

class TerraformWorldAction
{
    public function __construct(
        protected EntropyPressureService $pressureService,
        protected PhysicsMutator $physicsMutator,
        protected WorldEventLedger $eventLedger,
        protected \App\Domains\Narrative\Services\RealityNarrator $narrator
    ) {}

    /**
     * Execute a terraforming attempt from an aggressor world against a victim world.
     * 
     * @param World $aggressor The world initiating the change (e.g. World #3)
     * @param World $victim The world being rewritten (e.g. World #2)
     * @return array Result metrics
     */
    public function handle(World $aggressor, World $victim): array
    {
        // 1. Establish or Reinforce Connection
        $gate = GateChannel::firstOrCreate([
            'source_world_id' => $aggressor->id,
            'target_world_id' => $victim->id,
        ], [
            'type' => 'INVASION_RIFT',
            'stability' => 0.5, // Unstable
            'throughput' => 500.0, // High throughput
            'is_active' => true
        ]);

        // 2. Force Entropy Injection (Aggressive Flow)
        // Unlike passive pressure, this pushes entropy regardless of resistance
        $injectionAmount = 100.0; // Base injection
        
        // Cost to aggressor (Energy to open rift)
        $cost = $injectionAmount * 0.1; 
        $aggressor->entropy += $cost; // Aggressor generates heat doing this
        
        // Victim receives payload
        $victim->entropy += $injectionAmount;
        
        $aggressor->save();
        $victim->save();

        // 3. Rewrite Reality (Physics Mutation)
        // High exposure due to direct attack
        $mutationExposure = 0.5; // Significant drift
        $this->physicsMutator->drift($victim, $aggressor->physics_profile, $mutationExposure);

        // 4. Generate Narrative
        $dynamicDescription = $this->narrator->narrateInvasion($victim, $aggressor, $mutationExposure);

        // 5. Log Event
        $metadata = [
            'aggressor_id' => $aggressor->id,
            'entropy_injected' => $injectionAmount,
            'mutation_exposure' => $mutationExposure,
            'gate_id' => $gate->id
        ];

        $this->eventLedger->record(
            $victim, 
            \App\Domains\Saga\Enums\EpicEventType::TERRAFORM_EVENT->value, 
            $dynamicDescription,
            1.0, // Magnitude
            1.0, // Permanence
            'Public',
            $metadata
        );

        return [
            'status' => 'success',
            'entropy_transferred' => $injectionAmount,
            'mutation_applied' => true
        ];
    }
}
