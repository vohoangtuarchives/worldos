<?php

namespace WorldOS\Legacy\Application\Cosmology\Services;

use App\Models\Artifact;
use App\Models\UniverseModel;
use Illuminate\Support\Str;

class ArtifactService
{
    /**
     * Generate a new artifact from a specific universe event.
     */
    public function generateFromUniverse(UniverseModel $universe, string $reason): Artifact
    {
        $state = $universe->state_vector;
        $namePrefixes = ['Soul of', 'Fragment of', 'Core of', 'Echo of', 'Essence of'];
        $rarities = ['COMMON', 'RARE', 'LEGENDARY', 'COSMIC'];
        
        $rarity = $this->determineRarity($state);
        $name = $namePrefixes[array_rand($namePrefixes)] . ' ' . $universe->name;
        
        // Define power stats based on the universe's strengths at the time
        $powerStats = [
            'order_boost' => ($state['order'] ?? 0) * 0.2,
            'entropy_buff' => ($state['entropy'] ?? 0) * 0.15,
            'innovation_gain' => ($state['innovation'] ?? 0) * 0.1,
            'military_power' => ($state['military'] ?? 0) * 0.25,
        ];

        return Artifact::create([
            'name' => $name,
            'description' => "A physical manifestation of the $reason in the $universe->name. It vibrates with the frequency of its origin.",
            'origin_universe_id' => $universe->id,
            'owner_faction_id' => $universe->faction_id,
            'power_stats' => $powerStats,
            'rarity' => $rarity,
            'status' => 'IN_Bazaar',
        ]);
    }

    private function determineRarity(array $state): string
    {
        $sum = ($state['order'] ?? 0) + ($state['innovation'] ?? 0) + ($state['military'] ?? 0);
        if ($sum > 2.5) return 'COSMIC';
        if ($sum > 1.8) return 'LEGENDARY';
        if ($sum > 1.0) return 'RARE';
        return 'COMMON';
    }

    /**
     * Infuse an artifact into a target universe.
     */
    public function infuse(Artifact $artifact, UniverseModel $targetUniverse): bool
    {
        if ($artifact->status !== 'IN_Bazaar') return false;

        $targetState = $targetUniverse->state_vector;
        $stats = $artifact->power_stats;

        // Apply buffs
        $targetState['order'] = min(1.0, ($targetState['order'] ?? 0) + ($stats['order_boost'] ?? 0));
        $targetState['innovation'] = min(1.0, ($targetState['innovation'] ?? 0) + ($stats['innovation_gain'] ?? 0));
        
        $targetUniverse->state_vector = $targetState;
        $targetUniverse->save();

        $artifact->update(['status' => 'INFUSED', 'owner_faction_id' => $targetUniverse->faction_id]);

        return true;
    }

    public function getAvailableArtifacts()
    {
        return Artifact::where('status', 'IN_Bazaar')->latest()->get();
    }
}
