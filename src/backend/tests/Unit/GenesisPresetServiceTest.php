<?php

namespace Tests\Unit;

use WorldOS\Legacy\Application\Saga\Services\GenesisPresetService;
use Tests\TestCase;

class GenesisPresetServiceTest extends TestCase
{
    public function test_presets_include_archetype_seed_and_drift_profiles(): void
    {
        $service = app(GenesisPresetService::class);
        $preset = $service->find('cuu_trong_thien');

        $this->assertNotNull($preset);
        $this->assertSame('ascension_mysticism', $preset['archetype']);
        $this->assertSame('bounded_stochastic', $preset['seed_vector']['sampling_mode']);
        $this->assertArrayHasKey('ontology', $preset['seed_vector']);
        $this->assertArrayHasKey('drift_profile', $preset);
        $this->assertArrayHasKey('baseline_rate', $preset['drift_profile']);
    }

    public function test_all_presets_have_archetype_mapping(): void
    {
        $service = app(GenesisPresetService::class);

        foreach ($service->all() as $preset) {
            $this->assertArrayHasKey('archetype', $preset);
            $this->assertArrayHasKey('seed_vector', $preset);
            $this->assertArrayHasKey('drift_profile', $preset);
        }
    }

    public function test_archetype_mapping_coverage_matches_all_presets(): void
    {
        $service = app(GenesisPresetService::class);

        foreach ($service->allByCategory() as $category) {
            foreach ($category['presets'] as $preset) {
                $this->assertNotEmpty($preset['archetype']);
                $this->assertNotEmpty($preset['seed_vector']['ontology']);
                $this->assertNotEmpty($preset['seed_vector']['epistemic']);
                $this->assertNotEmpty($preset['seed_vector']['civilization']);
                $this->assertNotEmpty($preset['seed_vector']['energy']);
            }
        }
    }
}
