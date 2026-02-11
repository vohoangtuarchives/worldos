<?php

namespace App\Domains\World\Services;

use App\Domains\Power\Repositories\WorldEventLedgerRepository;
use App\Domains\Power\PowerStageRegistry;
use App\Domains\World\Policies\ContradictionPolicyResolver;
use App\Domains\World\Memory\ContradictionMemoryRepository;
use App\Domains\World\Myth;
use App\Domains\World\Scar;
use Illuminate\Support\Facades\DB;

class EventGate
{
    public function __construct(
        private WorldEventLedgerRepository $ledgerRepo,
        private PowerStageRegistry $stageRegistry,
        private ContradictionPolicyResolver $policyResolver,
        private ContradictionMemoryRepository $memoryRepo
    ) {}

    public function processEvent(int $worldId, array $eventData): array
    {
        // 1. Gate Check (Power Stage)
        $currentStageKey = DB::table('world_power_stages')->where('world_id', $worldId)->value('current_stage');
        $stageRules = $this->stageRegistry->getStageAndConstraint($currentStageKey ?? 'mundane');
        
        // Simple keyword check against Forbidden Terms
        $description = $eventData['description'] ?? '';
        foreach ($stageRules['forbidden_terms'] ?? [] as $term) {
            if (str_contains(strtolower($description), strtolower($term))) {
                // Hard Block by Physics/Reality
                return [
                    'allowed' => false,
                    'reason' => "Event violation: Term '$term' forbidden in '$currentStageKey' stage.",
                    'action' => 'blocked'
                ];
            }
        }

        // 2. Myth Check (Immutable Truths)
        $myths = Myth::where('world_id', $worldId)->get();
        foreach ($myths as $myth) {
            // Placeholder: semantic check would happen here via LLM or simple keyword match
            // For now, assume if event explicitly mentions contradicting a myth keyword
             if (str_contains($description, "RETCON: " . $myth->id)) {
                 return [
                    'allowed' => false,
                    'reason' => "Myth violation: {$myth->truth_statement}",
                    'action' => 'blocked'
                ];
             }
        }

        // 3. Scar Check (Permanent Constraints)
        $scars = Scar::where('world_id', $worldId)->get();
        foreach ($scars as $scar) {
            // Placeholder: location based check
             if (isset($eventData['location']) && $eventData['location'] === $scar->location_scope) {
                 return [
                    'allowed' => false,
                    'reason' => "Scar violation: {$scar->constraint_rule} at {$scar->location_scope}",
                    'action' => 'blocked'
                ];
             }
        }

        // 4. Contradiction Handling (The Core Logic)
        // Simulate a contradiction detection (Realistically, this is complex logic)
        $isContradiction = $eventData['is_contradiction'] ?? false;
        
        if ($isContradiction) {
             return $this->resolveContradiction($worldId, $eventData);
        }

        // 5. Pass - Log to Ledger
        $this->ledgerRepo->logEvent($eventData);

        return [
            'allowed' => true,
            'reason' => 'Passed all gates',
            'action' => 'logged'
        ];
    }

    private function resolveContradiction(int $worldId, array $eventData): array
    {
        $contradictionId = $eventData['contradiction_id'] ?? 'unknown';
        $severity = $eventData['severity'] ?? 'medium'; // low, medium, high, critical
        $gateBlocked = $eventData['gate_blocked'] ?? false;
        $contextHash = md5(json_encode($eventData));

        // A. Check Memory first
        $memory = $this->memoryRepo->findSimilarContext($worldId, $contextHash);
        if ($memory && $memory->effectiveness > 0.7) {
            // Reuse successful strategy
            return $this->applyStrategy($worldId, $memory->strategy_used, $eventData, $contextHash);
        }

        // B. Consult Policy
        $strategyConfig = $this->policyResolver->getStrategy($severity, $gateBlocked);

        if (!$strategyConfig) {
             // Default Fallback
             return ['allowed' => false, 'reason' => 'No strategy found', 'action' => 'rejected'];
        }
        
        // Extract strategy name from array key (simplified)
        $strategyName = key($strategyConfig) ?? 'accumulation'; // fallback

        // C. Apply Strategy & Learn
        return $this->applyStrategy($worldId, $strategyName, $eventData, $contextHash);
    }

    private function applyStrategy(int $worldId, string $strategy, array $eventData, string $hash): array
    {
        // Log decision immediately (Learning)
        $this->memoryRepo->logResolution($worldId, $eventData['contradiction_id'] ?? 'gen', $strategy, $hash);

        switch ($strategy) {
            case 'deflection':
                // Modify event outcome
                $eventData['description'] .= " (Altered by World Gate: Deflected to side-effect)";
                $eventData['magnitude'] *= 0.5;
                $this->ledgerRepo->logEvent($eventData);
                return ['allowed' => true, 'reason' => 'Deflected', 'action' => 'modified'];
            
            case 'accumulation':
                // Do not log active event, increase accumulators only
                // Real implementation would inspect/update world_power_stages pressure directly
                return ['allowed' => false, 'reason' => 'Accumulated Pressure', 'action' => 'delayed'];

            case 'sacrifice':
                 // Create a new Scar
                 Scar::create([
                     'world_id' => $worldId,
                     'location_scope' => $eventData['location'] ?? 'Global',
                     'constraint_rule' => "Sacrifice Scar: " . substr($eventData['description'], 0, 50),
                     'severity' => 0.8
                 ]);
                 $this->ledgerRepo->logEvent($eventData);
                 return ['allowed' => true, 'reason' => 'Sacrificed', 'action' => 'scar_created'];

            default:
                return ['allowed' => false, 'reason' => 'Unknown strategy', 'action' => 'blocked'];
        }
    }
}
