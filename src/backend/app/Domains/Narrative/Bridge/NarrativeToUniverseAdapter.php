<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge;

use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Mutation\UniverseMutationService;
use App\Domains\Narrative\Bridge\Contracts\NarrativePressureBridgeInterface;
use App\Domains\Narrative\Bridge\DTO\PressureSignal;
use App\Domains\Narrative\Bridge\DTO\StoryEvent;
use App\Domains\Narrative\Planning\StoryOutcomeDTO;
use App\Models\NarrativeSeries;
use Illuminate\Support\Facades\Log;

/**
 * Phase 4.3: Map narrative story events to Universe mutation. Single boundary: calls UniverseMutationService only.
 * Enabled when config mutation.narrative_affects_universe is true and series has universe_id.
 *
 * WorldOS 2.0 Clean: Preferred path is "pressure signal" (narrative_affects_via_pressure): inject pressure
 * into PhaseEngine so narrative creates conditions for collapse/phase transition instead of mutating vector
 * directly. When narrative_affects_via_pressure is implemented, this adapter may delegate to pressure API.
 */
final class NarrativeToUniverseAdapter
{
    public function __construct(
        private readonly UniverseMutationService $mutationService,
        private readonly CosmologyRepository $cosmologyRepository,
        private readonly NarrativePressureBridgeInterface $pressureBridge,
    ) {
    }

    /**
     * If enabled and series has universe_id, map events to one outcome and commit with magnitude limit.
     *
     * @param list<StoryEvent> $events
     */
    public function commitFromEvents(NarrativeSeries $series, array $events): bool
    {
        $universeId = $series->universe_id ?? null;
        if ($universeId === null || $universeId === '') {
            return false;
        }
        if ($this->cosmologyRepository->find($universeId) === null) {
            Log::warning('NarrativeToUniverseAdapter: universe not found', ['universe_id' => $universeId]);
            return false;
        }

        if (config('mutation.narrative_affects_via_pressure', false)) {
            $intensity = $this->eventsToPressureIntensity($events);
            $this->pressureBridge->injectPressure(new PressureSignal(
                $universeId,
                $intensity,
                'narrative',
                $series->id,
                null
            ));
            return true;
        }

        if (!config('mutation.narrative_affects_universe', false)) {
            return false;
        }

        $maxMagnitude = config('mutation.narrative_max_magnitude', 0.15);
        $outcome = $this->eventsToOutcome($events, $maxMagnitude);
        $worldId = $this->cosmologyRepository->getWorldIdForUniverse($universeId);

        try {
            $this->mutationService->commit($universeId, $outcome, null, $worldId);
            return true;
        } catch (\Throwable $e) {
            Log::warning('NarrativeToUniverseAdapter: commit failed', ['universe_id' => $universeId, 'message' => $e->getMessage()]);
            return false;
        }
    }

    private function eventsToPressureIntensity(array $events): float
    {
        if (empty($events)) {
            return 0.0;
        }
        $sum = 0.0;
        foreach ($events as $e) {
            $sum += $e->severity;
        }
        return min(1.0, $sum / count($events));
    }

    /**
     * Aggregate events into a single StoryOutcomeDTO (partial, intensity capped).
     */
    private function eventsToOutcome(array $events, float $maxMagnitude): StoryOutcomeDTO
    {
        if (empty($events)) {
            return new StoryOutcomeDTO(StoryOutcomeDTO::RESULT_PARTIAL, 0.0, 'narrative', false, null);
        }
        $avgSeverity = 0.0;
        foreach ($events as $e) {
            $avgSeverity += $e->severity;
        }
        $avgSeverity = $avgSeverity / count($events);
        $intensity = min($maxMagnitude, $avgSeverity);
        return new StoryOutcomeDTO(StoryOutcomeDTO::RESULT_PARTIAL, $intensity, 'narrative', true, null);
    }
}
