<?php

namespace App\Domains\Narrative\LLM\Services;

use Tuzy\Domain\Narrative\ValueObject\Intent;
use Illuminate\Support\Facades\Log;

class IntentParser
{
    /**
     * Parses raw JSON array from LLM into a valid Intent object.
     */
    public function parse(array $data): Intent
    {
        // 1. Validate Type
        $type = $data['type'] ?? 'IDLE';
        // Normalize
        $type = strtoupper($type);
        
        $allowed = ['PROBE', 'REVEAL', 'DEFLECT', 'EMOTIONAL_PRESSURE', 'IDLE'];
        if (!in_array($type, $allowed)) {
            Log::warning("LLM hallucinated invalid intent type: {$type}. Fallback to IDLE.");
            $type = 'IDLE';
        }

        // 2. Validate Payload
        $payload = $data['payload'] ?? [];
        if (!is_array($payload)) {
            $payload = ['raw' => $payload];
        }

        // 3. Confidence
        $confidence = (float)($data['confidence'] ?? 0.5);

        return new Intent($type, $payload, $confidence);
    }
}
