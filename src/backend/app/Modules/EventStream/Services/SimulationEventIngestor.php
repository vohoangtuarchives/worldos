<?php

namespace App\Modules\EventStream\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SimulationEventIngestor
{
    /**
     * Listens to the Redis stream/pub-sub for asynchronous events 
     * from the Rust Simulation Engine (e.g. macro phase transitions, anomalies)
     */
    public function listen()
    {
        Log::info("Starting Simulation Event Ingestor...");

        Redis::subscribe(['simulation_events'], function ($message) {
            Log::info("Received event from Engine: " . $message);
            $eventData = json_decode($message, true);

            if (!$eventData) {
                Log::error("Failed to decode simulation event.");
                return;
            }

            $this->processEvent($eventData);
        });
    }

    private function processEvent(array $eventData)
    {
        $eventType = $eventData['type'] ?? 'unknown';

        switch ($eventType) {
            case 'PHASE_TRANSITION_DETECTED':
                // Trigger narrative generation or stop the universe
                Log::info("Phase Transition Detected in Universe: " . $eventData['universe_id']);
                break;
            case 'GOVERNANCE_VIOLATION':
                // The engine halted a tick due to invariant violation
                Log::warning("Governance Violation in Universe: " . $eventData['universe_id']);
                break;
            default:
                Log::debug("Ignored event type: " . $eventType);
        }
    }
}
