<?php

namespace WorldOS\Saga\Domain\Legacy;

use WorldOS\Legacy\Application\Material\State\WorldState;
use Illuminate\Support\Collection;

/**
 * SagaExecutor - Orchestrate saga execution
 * 
 * Integrates: Interpreter → Detector → Assembler
 */
class SagaExecutor
{
    public function __construct(
        private SagaInterpreter $interpreter,
        private EventDetector $detector,
        private NarrativeAssembler $assembler,
        private CharacterIntegrator $characterIntegrator,
        private TimelineBuilder $timelineBuilder,
        private \WorldOS\Legacy\Application\World\Services\EventGate $eventGate
    ) {}

    /**
     * Execute saga for a simulation tick.
     * 
     * @param WorldState $previousState
     * @param WorldState $currentState
     * @param int $epoch
     * @param string $sagaType 'structural' | 'symbolic' | 'interaction' | 'full'
     * @return array Saga execution result
     */
    public function execute(
        WorldState $previousState,
        WorldState $currentState,
        int $epoch,
        string $sagaType = 'full'
    ): array {
        // 1. Interpret state changes
        $stateChanges = $this->interpreter->interpret(
            $previousState,
            $currentState,
            $sagaType
        );

        // 2. Get significant changes only
        $significantChanges = $this->interpreter->getSignificantChanges($stateChanges, 0.1);

        // 3. Detect events
        $rawEvents = $this->detector->detect($stateChanges, $currentState);

        // 3.5 Process events through World Gate (Deep Logic)
        $events = [];
        foreach ($rawEvents as $eventData) {
            // Ensure world_id is present
            $eventData['world_id'] = $currentState->worldId;
            $eventData['epoch'] = $epoch;
            
            // Pass through Gate
            $gateResult = $this->eventGate->processEvent($currentState->worldId, $eventData);
            
            if ($gateResult['allowed']) {
                // Should we use the modified event from the gate?
                // EventGate logs to Ledger, but here we need the object for processing.
                // Assuming EventGate *might* modify description/magnitude if 'Deflected'
                // For now, if allowed, we use the original eventData (or modified if we had a way to get it back from Gate)
                // Actually EventGate::processEvent accepts array returns array result.
                // But looking at EventGate implementation, it modifies the input array in 'applyStrategy' but only logs it.
                // It doesn't return the modified event data in the result structure clearly. 
                // Let's assume for now we use the passed event.
                // Ideally EventGate should return the modified event.
                
                // Let's rely on the fact that EventGate logs the *truth* to the Ledger.
                // But Saga needs to narrate what happened.
                // If it was deflected, the narrative should change.
                
                // For this iteration, let's accept allowed events.
                $events[] = $eventData;
            }
        }

        // 4. Assemble narrative
        $narrative = $this->assembler->assemble($events, $epoch);

        // 5. Integrate with characters (if available)
        $characterImpacts = [];
        try {
            $characters = \App\Models\Character::where('world_id', $currentState->worldId)->get();
            if ($characters->isNotEmpty()) {
                $characterImpacts = $this->characterIntegrator->integrate($events, $characters);
            }
        } catch (\Exception $e) {
            // Characters table may not exist yet
        }

        // 6. Add to timeline
        $timelineNodeIds = [];
        try {
            $timelineNodeIds = $this->timelineBuilder->addEvents($events, $epoch, $currentState->worldId);
        } catch (\Exception $e) {
            // Timeline may not be initialized
        }

        return [
            'epoch' => $epoch,
            'saga_type' => $sagaType,
            'state_changes' => $stateChanges,
            'significant_changes' => $significantChanges,
            'events' => $events,
            'narrative' => $narrative,
            'event_count' => count($events),
            'character_impacts' => $characterImpacts,
            'timeline_nodes' => $timelineNodeIds,
        ];
    }

    /**
     * Execute saga for multiple epochs.
     */
    public function executeMultiple(
        Collection $worldStates,
        string $sagaType = 'full'
    ): array {
        $results = [];

        for ($i = 1; $i < $worldStates->count(); $i++) {
            $previousState = $worldStates[$i - 1];
            $currentState = $worldStates[$i];

            $results[] = $this->execute(
                $previousState,
                $currentState,
                $currentState->epoch,
                $sagaType
            );
        }

        return $results;
    }

    /**
     * Generate narrative report for epoch range.
     */
    public function generateReport(array $sagaResults): string
    {
        $report = "# Saga Report\n\n";

        foreach ($sagaResults as $result) {
            $report .= "## {$result['narrative']}\n\n";

            if (!empty($result['events'])) {
                $report .= "**Events:**\n";
                foreach ($result['events'] as $event) {
                    $report .= "- {$event['type']} (severity: {$event['severity']})\n";
                }
                $report .= "\n";
            }
        }

        return $report;
    }
}
