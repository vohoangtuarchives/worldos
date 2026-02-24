<?php

namespace WorldOS\Legacy\Domain\Reader;

/**
 * VoteAggregator - Aggregate reader votes and calculate consensus
 * 
 * Supports multiple aggregation methods.
 */
class VoteAggregator
{
    /**
     * Aggregate votes for a choice.
     * 
     * @param string $choiceId
     * @param array $votes
     * @return array Aggregation result
     */
    public function aggregate(string $choiceId, array $votes): array
    {
        if (empty($votes)) {
            return [
                'choice_id' => $choiceId,
                'total_votes' => 0,
                'vote_counts' => [],
                'percentages' => [],
                'winner' => null,
            ];
        }

        $voteCounts = [];

        foreach ($votes as $vote) {
            $optionId = $vote['option_id'];
            $voteCounts[$optionId] = ($voteCounts[$optionId] ?? 0) + 1;
        }

        // Calculate percentages
        $total = array_sum($voteCounts);
        $percentages = [];

        foreach ($voteCounts as $optionId => $count) {
            $percentages[$optionId] = $count / $total;
        }

        // Find winner (most votes)
        arsort($voteCounts);
        $winner = array_key_first($voteCounts);

        return [
            'choice_id' => $choiceId,
            'total_votes' => $total,
            'vote_counts' => $voteCounts,
            'percentages' => $percentages,
            'winner' => $winner,
        ];
    }

    /**
     * Calculate weighted delta from votes.
     * 
     * @param array $options Choice options with deltas
     * @param array $percentages Vote percentages
     * @return array Weighted delta
     */
    public function calculateWeightedDelta(array $options, array $percentages): array
    {
        $weightedDelta = [];

        foreach ($options as $option) {
            $weight = $percentages[$option['id']] ?? 0;

            foreach ($option['delta'] as $field => $value) {
                $weightedDelta[$field] = ($weightedDelta[$field] ?? 0) + ($value * $weight);
            }
        }

        return $weightedDelta;
    }

    /**
     * Aggregate using simple majority (winner takes all).
     */
    public function aggregateSimpleMajority(string $choiceId, array $votes, array $options): array
    {
        $result = $this->aggregate($choiceId, $votes);

        if (!$result['winner']) {
            return array_merge($result, ['delta' => []]);
        }

        // Find winning option's delta
        $winningOption = collect($options)->firstWhere('id', $result['winner']);
        $delta = $winningOption['delta'] ?? [];

        return array_merge($result, ['delta' => $delta]);
    }

    /**
     * Aggregate using weighted average.
     */
    public function aggregateWeighted(string $choiceId, array $votes, array $options): array
    {
        $result = $this->aggregate($choiceId, $votes);

        if (empty($result['percentages'])) {
            return array_merge($result, ['delta' => []]);
        }

        $delta = $this->calculateWeightedDelta($options, $result['percentages']);

        return array_merge($result, ['delta' => $delta]);
    }
}
