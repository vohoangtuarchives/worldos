<?php

namespace WorldOS\Legacy\Application\Vietnamese\Services;

use WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero;
use WorldOS\Legacy\Domain\Vietnamese\Models\HeroEvent;
use WorldOS\Legacy\Domain\Vietnamese\Models\ScoringVersion;
use WorldOS\Legacy\Application\Vietnamese\Config\EventDimensionMapping;

class HeroScoringService
{
    private const NORMALIZATION_CONSTANT = 5.0;
    private const TIME_DECAY_LAMBDA = 0.0003;

    /**
     * Calculate all 12 dimension scores for a hero
     */
    public function calculateAllDimensions(VietnameseHero $hero, ?ScoringVersion $version = null): array
    {
        $version = $version ?? ScoringVersion::active();

        if (!$version) {
            throw new \RuntimeException('No active scoring version found');
        }

        $events = $hero->events;
        $rawScores = $this->initializeDimensions();

        foreach ($events as $event) {
            $contributions = $this->calculateEventContribution($event, $version);

            foreach ($contributions as $dimension => $value) {
                $rawScores[$dimension] += $value;
            }
        }

        // Apply time decay (except mythic dimension)
        $decayedScores = $this->applyTimeDecay($rawScores, $hero);

        // Normalize to [0, 1]
        $normalizedScores = $this->normalize($decayedScores, $version);

        return $normalizedScores;
    }

    /**
     * Calculate dimension contribution from a single event
     */
    private function calculateEventContribution(HeroEvent $event, ScoringVersion $version): array
    {
        $mapping = $version->event_dimension_map[$event->event_type] ?? [];
        $scaleFactor = EventDimensionMapping::SCALE_FACTORS[$event->scale] ?? 0.6;
        $durationBonus = log(1 + $event->duration_years);

        $contributions = [];

        foreach ($mapping as $dimension => $weight) {
            $contributions[$dimension] =
                $weight
                * $scaleFactor
                * $event->success
                * $durationBonus;
        }

        return $contributions;
    }

    /**
     * Apply exponential time decay to dimension scores
     */
    private function applyTimeDecay(array $scores, VietnameseHero $hero): array
    {
        if (!$hero->birth_year) {
            // No decay for mythical heroes
            return $scores;
        }

        $ageInYears = 2026 - $hero->birth_year;
        $decay = exp(-self::TIME_DECAY_LAMBDA * $ageInYears);

        foreach ($scores as $dim => $value) {
            if ($dim !== 'mythic') {
                // Mythic dimension doesn't decay
                $scores[$dim] *= $decay;
            }
        }

        return $scores;
    }

    /**
     * Normalize scores to [0, 1] range
     */
    private function normalize(array $scores, ScoringVersion $version): array
    {
        $constant = $version->normalization_constant;

        foreach ($scores as $dim => $value) {
            $scores[$dim] = min(1.0, round($value / $constant, 2));
        }

        return $scores;
    }

    /**
     * Calculate weighted impact score
     */
    public function calculateImpactScore(array $dimensions, ScoringVersion $version): float
    {
        $weights = $version->dimension_weights;
        $total = 0;
        $weightSum = 0;

        foreach ($dimensions as $dim => $value) {
            $weight = $weights[$dim] ?? 1.0;
            $total += $value * $weight;
            $weightSum += $weight;
        }

        return round($total / $weightSum, 2);
    }

    /**
     * Initialize all dimensions to 0
     */
    private function initializeDimensions(): array
    {
        return array_fill_keys(EventDimensionMapping::DIMENSIONS, 0.0);
    }

    /**
     * Rank heroes by specific dimension
     */
    public function rankByDimension(string $dimension, int $limit = 10)
    {
        return VietnameseHero::orderBy($dimension, 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get average dimension scores for an era
     */
    public function getEraProfile(int $era): array
    {
        $heroes = VietnameseHero::where('era', $era)->get();

        if ($heroes->isEmpty()) {
            return $this->initializeDimensions();
        }

        $profile = [];
        foreach (EventDimensionMapping::DIMENSIONS as $dim) {
            $profile[$dim] = round($heroes->avg($dim), 2);
        }

        return $profile;
    }
}
