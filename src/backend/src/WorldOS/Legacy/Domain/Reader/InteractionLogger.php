<?php

namespace WorldOS\Legacy\Domain\Reader;

use Illuminate\Support\Facades\DB;

/**
 * InteractionLogger - Log reader interactions for transparency
 * 
 * Provides full audit trail of reader influence.
 */
class InteractionLogger
{
    /**
     * Log reader vote.
     */
    public function logVote(
        string $worldId,
        int $epoch,
        string $choiceId,
        string $optionId,
        ?int $readerId = null,
        ?string $session = null
    ): void {
        DB::table('reader_interactions')->insert([
            'world_id' => $worldId,
            'epoch' => $epoch,
            'interaction_type' => 'choice',
            'choice_id' => $choiceId,
            'option_id' => $optionId,
            'reader_id' => $readerId,
            'reader_session' => $session,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log reader reaction.
     */
    public function logReaction(
        string $worldId,
        int $epoch,
        string $reactionType,
        ?int $readerId = null,
        ?string $session = null
    ): void {
        DB::table('reader_interactions')->insert([
            'world_id' => $worldId,
            'epoch' => $epoch,
            'interaction_type' => 'reaction',
            'reaction_type' => $reactionType,
            'reader_id' => $readerId,
            'reader_session' => $session,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log choice result.
     */
    public function logResult(string $worldId, int $epoch, array $result): void
    {
        DB::table('choice_results')->insert([
            'world_id' => $worldId,
            'epoch' => $epoch,
            'choice_id' => $result['choice_id'],
            'total_votes' => $result['total_votes'],
            'winning_option' => $result['winner'] ?? null,
            'vote_percentages' => json_encode($result['percentages'] ?? []),
            'applied_delta' => json_encode($result['delta'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get votes for epoch.
     */
    public function getVotesForEpoch(string $worldId, int $epoch): array
    {
        return DB::table('reader_interactions')
            ->where('world_id', $worldId)
            ->where('epoch', $epoch)
            ->where('interaction_type', 'choice')
            ->get()
            ->toArray();
    }

    /**
     * Get reactions for epoch.
     */
    public function getReactionsForEpoch(string $worldId, int $epoch): array
    {
        return DB::table('reader_interactions')
            ->where('world_id', $worldId)
            ->where('epoch', $epoch)
            ->where('interaction_type', 'reaction')
            ->get()
            ->toArray();
    }

    /**
     * Get choice results for epoch.
     */
    public function getResultsForEpoch(string $worldId, int $epoch): array
    {
        return DB::table('choice_results')
            ->where('world_id', $worldId)
            ->where('epoch', $epoch)
            ->get()
            ->toArray();
    }
}
