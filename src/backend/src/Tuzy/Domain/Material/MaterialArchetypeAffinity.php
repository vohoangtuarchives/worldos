<?php

namespace Tuzy\Domain\Material;

use Illuminate\Support\Facades\File;

class MaterialArchetypeAffinity
{
    private array $affinityMatrix;

    public function __construct()
    {
        $path = base_path('src/Tuzy/Application/Material/Data/affinity_matrix.json');
        $json = File::get($path);
        $this->affinityMatrix = json_decode($json, true) ?? [];
    }

    /**
     * Get archetype affinities for a given material code.
     */
    public function getAffinities(string $materialCode): ?array
    {
        return $this->affinityMatrix[$materialCode] ?? null;
    }

    /**
     * Get drift modifier for a material.
     */
    public function getDriftModifier(string $materialCode): float
    {
        return $this->affinityMatrix[$materialCode]['drift_modifier'] ?? 0.0;
    }

    /**
     * Get archetypes influenced by a material.
     */
    public function getInfluencedArchetypes(string $materialCode): array
    {
        return $this->affinityMatrix[$materialCode]['archetypes'] ?? [];
    }

    /**
     * Get activation threshold for a material.
     */
    public function getActivationThreshold(string $materialCode): float
    {
        return $this->affinityMatrix[$materialCode]['activation_threshold'] ?? 0.5;
    }

    /**
     * Check if archetype weight is high enough to activate a material.
     */
    public function canActivate(string $materialCode, array $archetypeWeights): bool
    {
        $affinities = $this->getAffinities($materialCode);
        if (!$affinities) {
            return false;
        }

        $threshold = $affinities['activation_threshold'];
        $relatedArchetypes = $affinities['archetypes'];

        // Check if any related archetype exceeds threshold
        foreach ($relatedArchetypes as $archetype) {
            if (isset($archetypeWeights[$archetype]) && $archetypeWeights[$archetype] >= $threshold) {
                return true;
            }
        }

        return false;
    }
}
