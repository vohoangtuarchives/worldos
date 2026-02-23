<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Services;

use WorldOS\Legacy\Domain\Cosmology\ValueObject\CosmicState;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\CivilizationState;
use WorldOS\Legacy\Domain\Cosmology\Enums\SocialClassType;

/**
 * SemanticProjector maps world state into a semantic vector space.
 * This enables narrative coherence and attractor matching.
 */
class SemanticProjector
{
    /**
     * Project a CosmicState into semantic space.
     * Returns: ['theme' => ..., 'archetype' => ..., 'mood' => ...]
     */
    public function projectWorldState(WorldSnapshot $snapshot): array
    {
        $entropy = $snapshot->cosmic->entropy;
        $energy = $snapshot->cosmic->energy;
        $stability = $snapshot->cosmic->stability;
        $strain = $snapshot->cosmic->strain;

        // Derive semantic attributes
        $theme = $this->deriveTheme($entropy, $energy, $stability);
        $archetype = $this->deriveArchetype($entropy, $stability, $strain);
        $mood = $this->deriveMood($entropy, $energy, $strain);

        return [
            'theme' => $theme,
            'archetype' => $archetype,
            'mood' => $mood,
            'entropy' => $entropy,
            'energy' => $energy,
            'stability' => $stability,
        ];
    }

    /**
     * Calculate cosine similarity between two semantic vectors.
     */
    public function calculateSimilarity(array $a, array $b): float
    {
        // Convert to numeric vectors
        $vecA = $this->toNumericVector($a);
        $vecB = $this->toNumericVector($b);

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vecA as $key => $valA) {
            $valB = $vecB[$key] ?? 0.0;
            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    private function deriveTheme(float $entropy, float $energy, float $stability): string
    {
        if ($entropy < 0.3 && $stability > 0.7) {
            return 'ORDER';
        } elseif ($entropy > 0.7) {
            return 'CHAOS';
        } elseif ($energy > 0.6 && $stability > 0.5) {
            return 'PROSPERITY';
        } else {
            return 'TRANSITION';
        }
    }

    private function deriveArchetype(float $entropy, float $stability, float $strain): string
    {
        if ($strain > 0.8) {
            return 'COLLAPSE';
        } elseif ($entropy > 0.6 && $stability < 0.4) {
            return 'TURBULENCE';
        } elseif ($entropy < 0.3 && $stability > 0.6) {
            return 'GOLDEN_AGE';
        } else {
            return 'EQUILIBRIUM';
        }
    }

    private function deriveMood(float $entropy, float $energy, float $strain): string
    {
        if ($strain > 0.7) {
            return 'DESPAIR';
        } elseif ($energy > 0.7 && $entropy < 0.4) {
            return 'HOPE';
        } elseif ($entropy > 0.6) {
            return 'FEAR';
        } else {
            return 'CALM';
        }
    }

    private function toNumericVector(array $semantic): array
    {
        // Extract only numeric values
        return array_filter($semantic, fn($val) => is_numeric($val));
    }
}
