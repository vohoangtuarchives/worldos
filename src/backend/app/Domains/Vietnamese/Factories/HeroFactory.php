<?php

namespace App\Domains\Vietnamese\Factories;

use App\Domains\Vietnamese\Models\WorldHero;
use App\Domains\Vietnamese\Services\VietnameseNameGenerator;
use App\Domains\Cosmology\Entities\WorldStateVector;
use Illuminate\Support\Str;

class HeroFactory
{
    public function __construct(
        private readonly VietnameseNameGenerator $nameGen
    ) {}

    public function createProceduralHero(string $worldId, array $worldDimensions, ?string $forceArchetype = null): WorldHero
    {
        $archetype = $forceArchetype ?? $this->determineArchetype($worldDimensions);
        $gender = in_array($archetype, ['WISE_QUEEN']) ? 'female' : 'male';
        
        $nameData = $this->nameGen->generateName($gender);
        $title = $this->nameGen->generateTitle($archetype);

        $stats = $this->generateStats($archetype, $worldDimensions);
        
        $hero = WorldHero::create([
            'world_id' => $worldId,
            'name' => $nameData['full_name'],
            'other_names' => [$title, $nameData['meaning']],
            'era' => 'procedural',
            'archetype' => $archetype,
            'biography' => "A hero born from the times. {$nameData['meaning']}.",
            'dimensions' => $stats,
            'impact_score' => array_sum($stats) / count($stats),
            'is_generated' => true,
            'status' => 'active'
        ]);

        return $hero;
    }

    private function determineArchetype(array $dims): string
    {
        // Simple Logic: 
        // High Chaos -> General/Rebel
        // High Order -> King/Philosopher
        // Low Cohesion -> Cultural

        $entropy = $dims[WorldStateVector::DIMENSION_ENTROPY] ?? 0.5;
        $order = $dims[WorldStateVector::DIMENSION_ORDER] ?? 0.5;
        $cohesion = $dims[WorldStateVector::DIMENSION_COHESION] ?? 0.5;

        if ($entropy > 0.7) return 'LEGENDARY_GENERAL';
        if ($entropy > 0.8) return 'REBEL_LEADER';
        if ($order > 0.8) return 'FOUNDING_KING';
        if ($cohesion < 0.4) return 'CULTURAL_HERO';
        
        return 'LEGENDARY_GENERAL'; // Default fallback
    }

    private function generateStats(string $archetype, array $dims): array
    {
        $base = [
            'military' => 0.5,
            'governance' => 0.5,
            'culture' => 0.5,
            'philosophy' => 0.5,
            'diplomacy' => 0.5,
            'economic' => 0.5,
            'rebellion' => 0.5,
        ];

        // Boost based on archetype
        switch ($archetype) {
            case 'LEGENDARY_GENERAL':
                $base['military'] = rand(8, 10) / 10;
                $base['rebellion'] = rand(6, 9) / 10;
                break;
            case 'FOUNDING_KING':
                $base['governance'] = rand(9, 10) / 10;
                $base['diplomacy'] = rand(7, 9) / 10;
                $base['military'] = rand(7, 9) / 10;
                break;
            case 'CULTURAL_HERO':
                $base['culture'] = rand(9, 10) / 10;
                $base['philosophy'] = rand(8, 10) / 10;
                break;
            case 'REBEL_LEADER':
                $base['rebellion'] = rand(9, 10) / 10;
                $base['military'] = rand(8, 10) / 10;
                break;
            default:
                break;
        }

        // Add Chaos Variance
        $entropy = $dims[WorldStateVector::DIMENSION_ENTROPY] ?? 0.5;
        foreach ($base as $k => $v) {
            $base[$k] = min(1.0, $v + (rand(-10, 10) / 100 * $entropy));
        }

        return $base;
    }
}
