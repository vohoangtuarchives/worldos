<?php

namespace App\Domains\WorldManagement\Services;

use App\Models\World;
use Illuminate\Support\Facades\DB;

class AIGovernanceService
{
    /**
     * Log an AI Generation attempt.
     */
    public function logGeneration(
        string $worldId,
        string $systemPrompt,
        string $userPrompt,
        string $responseContent,
        string $status,
        ?array $violations = null,
        int $attemptNumber = 1
    ): int {
        return DB::table('ai_generations')->insertGetId([
            'world_id' => $worldId,
            'prompt_hash' => md5($systemPrompt . $userPrompt),
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'response_content' => $responseContent,
            'status' => $status,
            'violations' => $violations ? json_encode($violations) : null,
            'attempt_number' => $attemptNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log extracted claims for a generation.
     */
    public function logClaims(int $generationId, array $claims, \App\Domains\World\Services\WorldLawValidator $validator, \App\Domains\World\ValueObjects\WorldLawProfile $profile): void
    {
        $data = [];
        $now = now();

        foreach ($claims as $claim) {
            // Re-validate individually to record specific reasons per claim if needed
            // Ideally validator returns detailed per-claim feedback, but here we just check
            $isValid = true;
            $reason = null;
            
            // Hacky individual check using Validator internals or just trusting previous result?
            // Let's do a simple check.
            $violations = [];
            // We need to wrap single claim in array
            if (!$validator->validateClaims($profile, [$claim], $violations)) {
                 $isValid = false;
                 $reason = implode(', ', $violations);
            }

            $data[] = [
                'generation_id' => $generationId,
                'claim_type' => $claim->type,
                'magnitude' => $claim->magnitude,
                'subject' => $claim->subject,
                'is_valid' => $isValid,
                'rejection_reason' => $reason,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($data)) {
            DB::table('ai_extracted_claims')->insert($data);
        }
    }
}
