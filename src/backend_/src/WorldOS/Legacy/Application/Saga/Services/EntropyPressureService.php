<?php

namespace WorldOS\Legacy\Application\Saga\Services;

use App\Models\GateChannel;
use App\Models\World;
use App\Models\SagaEntropyLedger;
use WorldOS\Legacy\Application\Saga\Services\PhysicsMutator;

class EntropyPressureService
{
    public function __construct(
        protected PhysicsMutator $physicsMutator
    ) {}

    /**
     * Process entropy exchange for all active gates connected to a world.
     */
    public function processInterWorldFlow(World $world, int $tick): void
    {
        // 1. Get active channels where this world is Source or Target
        $outgoing = GateChannel::where('source_world_id', $world->id)->where('is_active', true)->get();
        $incoming = GateChannel::where('target_world_id', $world->id)->where('is_active', true)->get();

        // 2. Process Outgoing (Dumping Entropy)
        foreach ($outgoing as $channel) {
            $this->processChannel($channel, $world, $channel->targetWorld, $tick);
        }

        // 3. Process Incoming (Receiving Entropy)
        foreach ($incoming as $channel) {
            $this->processChannel($channel, $channel->sourceWorld, $world, $tick);
        }
    }

    private function processChannel(GateChannel $channel, World $source, World $target, int $tick): void
    {
        // Physics: High Entropy -> Low Entropy (unless pumped)
        // For now, we assume simple diffusion defined by 'throughput'
        
        $pressure = $this->calculatePressure($source, $target);
        
        // If pressure is positive (Source > Target), flow happens naturally
        // If negative, flow might reverse or stop depending on Gate Type
        
        if ($pressure > 0) {
            $flowAmount = min($channel->throughput, $pressure * 0.1); // 10% equalization per tick max
            
            // Transfer
            $source->entropy -= $flowAmount;
            $target->entropy += $flowAmount;
            
            $source->save();
            $target->save();

            // Log ledger
            SagaEntropyLedger::create([
                'saga_id' => $source->saga_id ?? 1, // Assumption
                'world_id' => $target->id,
                'source_type' => 'GATE_INFLOW',
                'delta_entropy' => $flowAmount,
                'tick' => $tick,
                'metadata' => [
                    'source_world_id' => $source->id,
                    'channel_id' => $channel->id,
                    'pressure_differential' => $pressure
                ]
            ]);

            // Side Effect: Physics Mutation on Target
            // Receiving entropy from a high-entropy source corrupts physics
            $exposure = $flowAmount / 100.0; // Arbitrary scale
            if ($exposure > 0.01) {
                // Target drafts towards Source's profile
                $this->physicsMutator->drift($target, $source->physics_profile, $exposure);
            }
        }
    }

    /**
     * Calculate Entropy Pressure Differential (Voltage).
     * Positive = Source is higher entropy.
     */
    public function calculatePressure(World $source, World $target): float
    {
        // Since entropy is normalized to 0.0 - 1.0 in the simulation,
        // pressure is simply the differential between the two worlds.
        return $source->entropy - $target->entropy;
    }
}
