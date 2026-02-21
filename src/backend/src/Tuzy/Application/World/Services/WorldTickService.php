<?php

namespace Tuzy\Application\World\Services;

use App\Models\World;
use Illuminate\Support\Facades\Log;

class WorldTickService
{
    public function processTick(World $world, int $tick): void
    {
        // 1. Load Current State
        $currentEntropy = $world->entropy ?? 0;
        $currentTick = $tick;

        // 2. Evolution Logic
        $entropyDelta = 0.001; 
        
        // 3. Apply Changes
        $world->entropy = min(1.0, $currentEntropy + $entropyDelta);
        $world->current_tick = $currentTick;
        
        // 4. Generate History / Events
        $this->generateHistory($world, $currentTick);

        // 5. Check Extinction / Collapse
        if ($world->entropy >= 1.0 && $world->status !== 'collapsed') {
            $this->triggerCollapse($world);
        }

        // 6. Check Prophet Spawn
        if ($world->status === 'collapsed' && !$world->is_prophet) {
            if (rand(0, 100) < 5) { 
                $this->spawnProphet($world);
            }
        }

        // 7. Apply Meta Impulses
        $this->applyMetaImpulses($world, $currentTick);

        // 8. Check Canonization
        $this->checkCanonization($world);

        $world->save();

        // 9. Capture Snapshot
        // $snapshotService = app(\Tuzy\Application\Replay\Services\SnapshotService::class);
        // $snapshotService->captureWorld($world, 'auto-run');
        
        Log::info("World {$world->id} processed tick {$tick}. Entropy: {$world->entropy}");

        // 10. AI Advisor (Phase 6)
        if ($tick % 50 === 0) {
            \App\Jobs\Cosmology\RunStyleAdvisorJob::dispatch($world->id);
        }
    }

    private function generateHistory(World $world, int $tick): void
    {
        // 10% chance per tick to generate a minor event
        if (rand(0, 100) < 10) {
            $ledger = app(\Tuzy\Application\World\Services\WorldEventLedger::class);
            $eventTypes = ['Discovery', 'Conflict', 'Alliance', 'Catastrophe', 'Festival'];
            $type = $eventTypes[array_rand($eventTypes)];
            
            $ledger->record(
                $world,
                $type,
                "A {$type} occurred during the Age of " . ($world->current_era ?? 'Dawn'),
                0.2, // Magnitude
                0.5, // Permanence
                'Public'
            );
        }

        // 2% chance for a Major Shock Event
        if (rand(0, 100) < 2) {
            $shockGen = app(\Tuzy\Application\World\Services\ShockEventGenerator::class);
            // Need aggregates, but for now let's hack it with direct call to ledger or simplified shock
            // $shock = $shockGen->generate($worldAggregate, $entropyScore, $tick); 
            // Simplified:
            $ledger = app(\Tuzy\Application\World\Services\WorldEventLedger::class);
            $ledger->record(
                $world,
                'SHOCK_EVENT',
                "A Major Transformation shakes the foundations of the world.",
                0.8,
                1.0,
                'Public'
            );
        }
    }

    private function applyMetaImpulses(World $world, int $tick): void
    {
        $impulses = \App\Models\MetaImpulse::where('active_until_tick', '>=', $tick)->get();
        
        foreach ($impulses as $impulse) {
            if ($impulse->type === 'MASS_EXTINCTION') {
                $severity = $impulse->strength;
                // Increase entropy or kill population based on severity
                $world->entropy += $severity * 0.1;
                $world->cosmic_energy -= $severity * 0.2; 
                
                if ($world->entropy >= 1.0) {
                    $this->triggerCollapse($world);
                }
            }
        }
    }

    private function checkCanonization(World $world): void
    {
        if ($world->status !== 'active') return;

        // Load MetaLayer to check
        $metaLayerState = \App\Models\MetaLayerState::instance();
        $metaLayer = new \Tuzy\Domain\Meta\Aggregates\MetaLayer();
        $metaLayer->hydrate($metaLayerState->toArray());

        // Dummy Myth Profile for now
        $mythProfile = ['intensity' => 0.9, 'heroism' => 0.8]; 

        $sacred = $metaLayer->attemptCanonization($world, $mythProfile);
        
        if ($sacred) {
            Log::info("World {$world->id} archetype canonized: {$sacred->name}");
            // Mark world to avoid re-canonization logic if policy doesn't handle it
            // Policy handles it via is_prophet check, but we should update world meta too?
            // Actually policy checks is_prophet, so we should set it if it becomes a Prophet world later.
            // Canonization doesn't make the world a prophet immediately, but creates a SacredArchetype.
        }
    }

    private function triggerCollapse(World $world): void
    {
        $world->status = 'collapsed';
        Log::info("World {$world->id} has collapsed at tick {$world->current_tick}");
        
        // Send Impulse to Meta
        // TODO: Outbox Message
    }

    private function spawnProphet(World $world): void
    {
        $world->is_prophet = true;
        // Reset entropy slightly for the prophet phase?
        $world->entropy = 0.5;
        $world->status = 'active'; // Reborn
        Log::info("World {$world->id} has spawned a Prophet at tick {$world->current_tick}");
        
        // Send Impulse to Meta
        // We could also Link to the SacredArchetype here if one exists
    }
}
