<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\ValueObjects;

/**
 * Attractor Type — the narrative gravity well archetype.
 *
 * Each type represents a civilizational trajectory that pulls
 * the Universe's state vector toward a specific basin.
 */
enum AttractorType: string
{
    case TECHNOLOGICAL_SINGULARITY = 'technological_singularity';
    case FEUDAL_STAGNATION = 'feudal_stagnation';
    case DEMOCRATIC_EQUILIBRIUM = 'democratic_equilibrium';
    case SPIRITUAL_TRANSCENDENCE = 'spiritual_transcendence';
    case COLLAPSE_SPIRAL = 'collapse_spiral';

    /**
     * Get the state vector conditions that define this attractor's basin.
     *
     * @return array<string, array{min: float, max: float}>
     */
    public function basinConditions(): array
    {
        return match ($this) {
            self::TECHNOLOGICAL_SINGULARITY => [
                'innovation' => ['min' => 0.8, 'max' => 1.0],
                'entropy' => ['min' => 0.3, 'max' => 0.7],
            ],
            self::FEUDAL_STAGNATION => [
                'order' => ['min' => 0.7, 'max' => 1.0],
                'innovation' => ['min' => 0.0, 'max' => 0.3],
                'inequality' => ['min' => 0.6, 'max' => 1.0],
            ],
            self::DEMOCRATIC_EQUILIBRIUM => [
                'legitimacy' => ['min' => 0.7, 'max' => 1.0],
                'inequality' => ['min' => 0.0, 'max' => 0.4],
                'cohesion' => ['min' => 0.5, 'max' => 1.0],
            ],
            self::SPIRITUAL_TRANSCENDENCE => [
                'cohesion' => ['min' => 0.8, 'max' => 1.0],
                'order' => ['min' => 0.5, 'max' => 0.8],
            ],
            self::COLLAPSE_SPIRAL => [
                'entropy' => ['min' => 0.8, 'max' => 1.0],
                'cohesion' => ['min' => 0.0, 'max' => 0.3],
            ],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::TECHNOLOGICAL_SINGULARITY => 'Technological Singularity',
            self::FEUDAL_STAGNATION => 'Feudal Stagnation',
            self::DEMOCRATIC_EQUILIBRIUM => 'Democratic Equilibrium',
            self::SPIRITUAL_TRANSCENDENCE => 'Spiritual Transcendence',
            self::COLLAPSE_SPIRAL => 'Collapse Spiral',
        };
    }
}
