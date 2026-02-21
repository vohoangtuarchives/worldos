<?php

namespace Tests\Unit;

use Tuzy\Application\World\Services\MaterialQuery;
use Tuzy\Application\World\Services\MaterialService;
use Tuzy\Application\World\Services\OntologyService;
use Tuzy\Application\World\Services\PresetVersionService;
use App\Models\World\World;
use App\Models\World\WorldMaterialOverride;
use App\Models\World\WorldPreset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MaterialOverridesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hybrid_overrides()
    {
        // 1. Setup Base V1
        $versionService = app(PresetVersionService::class);
        $materialService = app(MaterialService::class);
        
        $preset = WorldPreset::create([
            'code' => 'hybrid_test',
            'name' => 'Hybrid Test',
            'is_active' => true,
            'config' => [],
            'power_policy' => 'linear',
            'resource_policy' => 'infinite',
            'conflict_policy' => 'none',
            'escalation_policy' => 'passive',
        ]);
        $v1 = $versionService->createInitialVersion($preset);
        
        $fireball = $materialService->createMaterial($v1, ['type' => 'spell', 'slug' => 'fireball', 'name' => 'Fireball', 'power_scale' => 10]);
        $heal = $materialService->createMaterial($v1, ['type' => 'spell', 'slug' => 'heal', 'name' => 'Heal', 'power_scale' => 5]);

        // 2. Create World linked to V1
        $world = World::create([
            'name' => 'My Custom World',
            'preset_version_id' => $v1->id,
            'preset_id' => $preset->id // Wait, World model in migration didn't have preset_id, let me check. 
            // The migration I wrote for world_overrides_tables CHECKED for 'worlds' table. 
            // My assumption was I might need to create it. I created it with preset_version_id. 
            // But World Model might expect preset_id too if I reuse existing? 
            // Let's rely on what I just created: preset_version_id.
        ]);

        // 3. Create Overrides
        
        // A. DISABLE 'Heal'
        WorldMaterialOverride::create([
            'id' => Str::uuid()->toString(),
            'world_id' => $world->id,
            'preset_material_id' => $heal->id,
            'override_mode' => 'disable'
        ]);

        // B. MODIFY 'Fireball' (Rename to 'Hellfire', double power)
        WorldMaterialOverride::create([
            'id' => Str::uuid()->toString(),
            'world_id' => $world->id,
            'preset_material_id' => $fireball->id,
            'override_mode' => 'modify',
            'name' => 'Hellfire',
            'power_scale_modifier' => 2.0
        ]);

        // C. EXTEND (Add 'Unique Sword')
        WorldMaterialOverride::create([
            'id' => Str::uuid()->toString(),
            'world_id' => $world->id,
            'override_mode' => 'extend',
            'name' => 'Unique Sword',
            'slug' => 'unique_sword',
            'power_scale_modifier' => 50 // Base power for extension
        ]);

        // 4. Query & Verify
        $results = MaterialQuery::for($v1)->withOverrides($world->id);

        // Should have 2 items (Hellfire, Unique Sword). Heal is gone.
        $this->assertCount(2, $results);

        // Verify Disable
        $this->assertNull($results->firstWhere('slug', 'heal'));

        // Verify Modify
        $hellfire = $results->firstWhere('slug', 'fireball'); // ID/slug remains same unless changed. I didn't change slug in modify override above, only name.
        $this->assertNotNull($hellfire);
        $this->assertEquals('Hellfire', $hellfire->name);
        $this->assertEquals(20, $hellfire->power_scale); // 10 * 2.0

        // Verify Extend
        $sword = $results->firstWhere('slug', 'unique_sword');
        $this->assertNotNull($sword);
        $this->assertEquals('Unique Sword', $sword->name);
        $this->assertEquals(50, $sword->power_scale);
    }
}
