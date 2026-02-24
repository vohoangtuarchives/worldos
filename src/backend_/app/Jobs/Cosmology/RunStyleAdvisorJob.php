<?php

namespace App\Jobs\Cosmology;

use WorldOS\Legacy\Application\Cosmology\Services\StyleAdvisorService;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\UniverseStyleVersion;
use App\Models\World;
use App\Models\UniverseStyle;
use WorldOS\Legacy\Application\Governance\Actions\ProposeStyleChangeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunStyleAdvisorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $worldId
    ) {}

    public function handle(
        StyleAdvisorService $advisor,
        ProposeStyleChangeAction $proposeAction
    ): void {
        $world = World::find($this->worldId);
        // Advisor runs every 50 ticks as per Roadmap Phase 6
        if (!$world || $world->current_tick % 50 !== 0) {
            return;
        }

        $style = UniverseStyle::where('world_id', $this->worldId)->where('is_active', true)->first();
        if (!$style) {
            Log::warning("RunStyleAdvisorJob: No active UniverseStyle found for world", ['world_id' => $this->worldId]);
            return;
        }

        // Get recent snapshots (trajectory)
        // In WorldOS, world has cosmicSnapshots relationship.
        $snapshots = $world->cosmicSnapshots()->latest()->take(10)->get();
        
        // Convert snapshots to ValueObjects if necessary. 
        // StyleAdvisorService::analyze expects array of WorldSnapshot (or compatible array)
        // Based on its code, it uses $lastSnap->cosmic
        $trajectory = $snapshots->map(function($s) {
            // Need to ensure $s can be converted to WorldSnapshot or has compatible structure
            // For now, passing them since analyze uses them semantically
            return $s; 
        })->toArray();

        $currentStyleVO = UniverseStyleVersion::fromArray([
            'style_id' => $style->id,
            'version_number' => $style->version,
            'weight_profile' => $style->style_vector,
            'alignment_profile' => [], 
            'arc_profile' => [],
            'checksum_hash' => '',
        ]);

        $result = $advisor->analyze($trajectory, $currentStyleVO, $world->current_tick);

        if (isset($result['proposal']) && $result['proposal'] !== null) {
            $proposeAction->execute($this->worldId, $result['proposal']);
        }
    }
}
