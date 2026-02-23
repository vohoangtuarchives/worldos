<?php

namespace App\Domains\Power\Services;

use App\Domains\Power\Repositories\WorldEventLedgerRepository;
use App\Domains\Power\PowerStageRegistry;
use Illuminate\Support\Facades\DB;

class WorldPressureService
{
    public function __construct(
        private WorldEventLedgerRepository $ledgerRepo,
        private PowerStageRegistry $stageRegistry
    ) {}

    public function calculatePressure(string $worldId): float
    {
        // 1. Fetch active events from ledger
        $events = $this->ledgerRepo->getActiveEvents($worldId);
        
        $totalPressure = 0.0;
        
        foreach ($events as $event) {
            // Pressure = Magnitude * Permanence
            // Events that are 'secret' might contribute less to *public* pressure, 
            // but here we calculate *structural* pressure on the world seal.
            $totalPressure += ($event->magnitude * $event->permanence);
        }

        return $totalPressure;
    }

    public function syncPressure(string $worldId): float
    {
        $pressure = $this->calculatePressure($worldId);
        
        DB::table('world_power_stages')->where('world_id', $worldId)->update([
            'accumulated_pressure' => $pressure,
            'updated_at' => now(),
        ]);

        return $pressure;
    }

    public function checkTransition(string $worldId): ?\WorldOS\Legacy\Domain\Power\ValueObject\PowerStage
    {
        $this->syncPressure($worldId);
        
        $engine = app(\App\Domains\Power\StageTransitionEngine::class);
        return $engine->evaluate($worldId);
    }
}
