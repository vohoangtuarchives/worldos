<?php

namespace WorldOS\Legacy\Application\Genesis\Services;

use App\Models\GenesisSeed;
use Illuminate\Support\Str;

class WorldSeedGenerator
{
    /**
     * Generate a new Genesis Seed to birth a world.
     */
    public function generate(?string $seedString = null): GenesisSeed
    {
        $seedString = $seedString ?? Str::random(16);
        
        // Seed the PRNG (not cryptographically secure, but consistent for "seeds")
        // PHP's srand/rand interaction is global, let's just use hash based generation
        // for determinism if a seed is provided, or random if not.
        
        $hash = md5($seedString);
        $numerals = array_map('hexdec', str_split(substr($hash, 0, 12), 4));
        
        // 1. Generate Metaphysics Vector (Tinh - Khi - Than)
        // Normalize to sum ~ 1.0, but allow some variation in total power
        $tinh = ($numerals[0] % 100) / 100;
        $khi = ($numerals[1] % 100) / 100;
        $than = ($numerals[2] % 100) / 100;
        
        $total = $tinh + $khi + $than;
        $vector = [
            'tinh' => round($tinh / $total, 2),
            'khi' => round($khi / $total, 2),
            'than' => round($than / $total, 2),
        ];

        // 2. Instability Index (Chaos factor)
        // High instability = More likely to have Conflict Patterns of "Systemic Collapse"
        $instability = ($numerals[0] ^ $numerals[1]) % 100 / 100;

        // 3. Tags (Flavor)
        $possibleTags = ['grimdark', 'high_fantasy', 'cyber_cultivation', 'lovecraftian', 'utopian', 'post_apoc'];
        $tags = [];
        if ($instability > 0.7) $tags[] = 'grimdark';
        if ($vector['khi'] > 0.6) $tags[] = 'high_fantasy';
        if ($vector['tinh'] > 0.6) $tags[] = 'martial_world';
        if (($numerals[2] % 2) === 0) $tags[] = $possibleTags[$numerals[2] % count($possibleTags)];
        
        return GenesisSeed::create([
            'id' => Str::uuid(),
            'metaphysics_vector' => $vector,
            'instability_index' => $instability,
            'seed_string' => $seedString,
            'tags' => array_values(array_unique($tags)),
        ]);
    }
}
