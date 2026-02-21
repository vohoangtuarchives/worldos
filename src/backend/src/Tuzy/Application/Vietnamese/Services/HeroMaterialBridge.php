<?php

namespace Tuzy\Application\Vietnamese\Services;

use Tuzy\Domain\Vietnamese\Models\VietnameseHero;
use Tuzy\Domain\Material\MaterialInstance;
use Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class HeroMaterialBridge
{
    public function __construct(
        private readonly MaterialRepositoryInterface $materialRepo
    ) {}

    /**
     * Calculate and apply material effects based on a Hero's emergence.
     * Returns an array of effects applied for logging.
     * 
     * @param VietnameseHero|WorldHero $hero
     */
    public function processHeroEmergence($hero, World $world): array
    {
        $effects = [];
        $archetype = $hero->archetype;

        // 1. Define Archetype -> Material Mappings
        $mappings = $this->getArchetypeMappings();

        if (!isset($mappings[$archetype])) {
            return $effects;
        }

        $targetMaterials = $mappings[$archetype];

        foreach ($targetMaterials as $materialCode => $modifier) {
            // Find or Create Material Instance in this World
            $material = $this->materialRepo->findByCode($materialCode);
            if (!$material) {
                continue;
            }

            // Check if instance exists, if not create/seed it
            $instance = $this->materialRepo->getInstancesForWorld($world->id)
                ->where('material.code', $materialCode)
                ->first();

            if (!$instance) {
                $instance = $this->materialRepo->createInstance($material, $world->id, [
                    'strength_level' => 0.1, // Start low if new
                    'activation_epoch' => $world->tick ?? 0
                ]);
            }

            // Apply impact based on Hero's specific dimension score
            // e.g. Military score scales the impact on VIOLENCE
            $scaleFactor = $this->getDimensionScale($hero, $materialCode);
            $finalImpact = $modifier * $scaleFactor;

            // Apply to instance strength
            $newStrength = $instance->strength_level + $finalImpact;
            $newStrength = max(0.0, min(1.0, $newStrength)); // Clamp 0-1

            $this->materialRepo->updateInstance($instance, [
                'strength_level' => $newStrength
            ]);

            $effects[$materialCode] = $finalImpact;
        }

        return $effects;
    }

    // ... mapping method ...

    private function getDimensionScale($hero, string $materialCode): float
    {
        // Simple heuristic: match material keyword to dimension
        // If no match, default to Impact Score
        $dims = $hero->dimensions;
        // Handle array casting if accessing property directly doesn't work (Eloquent accessor vs array)
        if (is_string($dims)) $dims = json_decode($dims, true);
        
        $map = [
            'VIOLENCE' => 'military',
            'ORDER' => 'governance',
            'CULTURE' => 'culture',
            'WISDOM' => 'philosophy',
            'PROSPERITY' => 'economic',
            'CHAOS' => 'rebellion',
            'DIPLOMACY' => 'diplomacy'
        ];

        foreach ($map as $key => $dim) {
            if (str_contains($materialCode, $key) && isset($dims[$dim])) {
                return 0.5 + ($dims[$dim] / 2); // 0.5 to 1.0 multiplier based on stats
            }
        }

        return 1.0; // Default scaling
    }
}
