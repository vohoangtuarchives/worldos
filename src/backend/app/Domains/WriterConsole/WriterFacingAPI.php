<?php

namespace App\Domains\WriterConsole;

use App\Domains\Saga\Saga;

/**
 * Writer Facing API
 * 
 * Simplified API surface for the frontend/writer UI.
 * Facades the complex underlying domains (Saga, Historian, Kernel).
 */
class WriterFacingAPI
{
    private TerminologyMapper $mapper;

    public function __construct()
    {
        $this->mapper = new TerminologyMapper();
    }

    /**
     * Get world state in writer terms
     */
    public function getWorldState(array $technicalState): array
    {
        return [
            'mood' => $this->analyzeMood($technicalState['archetypes'] ?? []),
            'tension' => $this->calculateTension($technicalState),
            'themes' => $this->mapThemes($technicalState['archetypes'] ?? []),
            'stability' => $this->mapStability($technicalState['legitimacy'] ?? 0.5),
        ];
    }

    /**
     * Map technical archetypes to "Active Themes"
     */
    private function mapThemes(array $archetypes): array
    {
        return collect($archetypes)
            ->filter(fn($w) => $w['weight'] > 0.4)
            ->map(fn($w) => [
                'name' => $this->mapper->mapArchetype($w['key']),
                'intensity' => $this->mapper->mapWeightToMood($w['key'], $w['weight']),
                'trend' => $this->mapper->mapDriftDescription($w['drift'] ?? 0),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Analyze overall mood
     */
    private function analyzeMood(array $archetypes): string
    {
        // Logic to determine overall "vibe" from combination of dominant archetypes
        return 'Tense and Expectant'; // Placeholder
    }

    /**
     * Calculate narrative tension
     */
    private function calculateTension(array $state): string
    {
        $legitimacy = $state['legitimacy'] ?? 1.0;
        
        return match(true) {
            $legitimacy < 0.2 => 'Breaking Point',
            $legitimacy < 0.4 => 'High Tension',
            $legitimacy < 0.7 => 'Unsettled',
            default => 'Calm',
        };
    }

    /**
     * Map stability description
     */
    private function mapStability(float $legitimacy): string
    {
        return match(true) {
            $legitimacy > 0.8 => 'Golden Age',
            $legitimacy > 0.6 => 'Stable Era',
            $legitimacy > 0.4 => 'Time of Troubles',
            $legitimacy > 0.2 => 'Crisis',
            default => 'Collapse Imminent',
        };
    }
}
