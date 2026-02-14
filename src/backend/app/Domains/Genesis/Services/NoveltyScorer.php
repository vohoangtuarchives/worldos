<?php

namespace App\Domains\Genesis\Services;

use App\Models\StoryBlueprint;
use Illuminate\Support\Collection;

class NoveltyScorer
{
    /**
     * Calculate how unique a blueprint is compared to the existing corpus.
     * Returns a float 0.0 (Clone) to 1.0 (Completely Alien).
     */
    public function calculateGlobalNovelty(StoryBlueprint $blueprint): float
    {
        // Get recent blue prints to compare against (e.g., last 50)
        $recentBlueprints = StoryBlueprint::where('id', '!=', $blueprint->id)
            ->latest()
            ->take(50)
            ->get();

        if ($recentBlueprints->isEmpty()) {
            return 1.0; // First of its kind!
        }

        $similarities = [];
        foreach ($recentBlueprints as $existing) {
            $similarities[] = $this->calculateSimilarity($blueprint, $existing);
        }

        // Novelty is the inverse of the maximum similarity found.
        // If the most similar existing story is 90% similar, novelty is 0.1.
        $maxSimilarity = max($similarities);
        
        return 1.0 - $maxSimilarity;
    }

    private function calculateSimilarity(StoryBlueprint $a, StoryBlueprint $b): float
    {
        $score = 0.0;
        $weights = [
            'theme' => 0.25,
            'archetype' => 0.25,
            'power' => 0.20,
            'conflict' => 0.15,
            'structure' => 0.15,
        ];

        // 1. Theme Identity (Binary match for now, could be vector distance later)
        if ($a->theme_id === $b->theme_id) {
            $score += $weights['theme'];
        }

        // 2. Archetype Pairing (Both must match for full score)
        $archetypeMatch = 0;
        if ($a->protagonist_archetype_id === $b->protagonist_archetype_id) $archetypeMatch += 0.5;
        if ($a->antagonist_archetype_id === $b->antagonist_archetype_id) $archetypeMatch += 0.5;
        $score += $weights['archetype'] * $archetypeMatch;

        // 3. Power System
        if ($a->power_system_id === $b->power_system_id) {
            $score += $weights['power'];
        }

        // 4. Conflict
        if ($a->conflict_id === $b->conflict_id) {
            $score += $weights['conflict'];
        }

        // 5. Structure Vector (Simple Euclidean distance on pacing/tone)
        // Assume structure vector has 'tone' (string) and 'complexity' (float)
        $structSim = 0.0;
        if (($a->structure_vector['tone'] ?? '') === ($b->structure_vector['tone'] ?? '')) {
            $structSim += 0.5;
        }
        
        $diffComplex = abs(($a->structure_vector['complexity'] ?? 0) - ($b->structure_vector['complexity'] ?? 0));
        // If diff is 0, sim is 0.5. If diff is 1, sim is 0.
        $structSim += 0.5 * (1.0 - min(1.0, $diffComplex));

        $score += $weights['structure'] * $structSim;

        return $score;
    }
}
