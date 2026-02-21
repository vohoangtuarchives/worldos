<?php

namespace Tuzy\Application\Governance\Actions;

use App\Models\StyleProposal;
use Tuzy\Domain\Governance\Events\StyleProposalCreated;
use Illuminate\Support\Facades\Log;

class ProposeStyleChangeAction
{
    public function execute(string $worldId, array $proposalData): StyleProposal
    {
        $proposal = StyleProposal::create([
            'world_id' => $worldId,
            'proposed_adjustments' => $proposalData['weight_adjustments'] ?? [],
            'reasoning' => implode("\n", $proposalData['reason'] ?? []),
            'predicted_improvement' => $proposalData['predicted_gi_improvement'] ?? 0.0,
            'status' => 'PENDING',
        ]);

        Log::info("Style proposal created by Advisor", [
            'world_id' => $worldId,
            'proposal_id' => $proposal->id,
        ]);

        StyleProposalCreated::dispatch($proposal);

        return $proposal;
    }
}
