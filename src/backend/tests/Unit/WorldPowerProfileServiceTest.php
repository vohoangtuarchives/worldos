<?php

namespace Tests\Unit;

use App\Domains\Saga\Services\GenesisPresetService;
use App\Domains\World\Services\WorldPowerProfileService;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldPowerProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_profile_creates_record_from_preset(): void
    {
        $world = World::factory()->create([
            'config' => ['current_stage' => 'mundane'],
        ]);

        $preset = app(GenesisPresetService::class)->find('cuu_trong_thien');

        $service = app(WorldPowerProfileService::class);
        $profile = $service->bootstrapProfile($world, $preset);

        $this->assertNotNull($profile);
        $this->assertEquals($world->id, $profile->world_id);
        $this->assertEquals('cuu_trong_thien', $profile->schema_key);
        $this->assertEquals('SPIRITUAL_QI', $profile->parameters['power_system']);
        $this->assertEquals('ascension_mysticism', $profile->parameters['archetype']);
        $this->assertArrayHasKey('seed_vector', $profile->parameters);
        $this->assertArrayHasKey('drift_profile', $profile->parameters);
    }
}
