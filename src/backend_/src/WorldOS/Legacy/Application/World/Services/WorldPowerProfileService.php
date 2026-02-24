<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World;
use App\Models\WorldPowerProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WorldPowerProfileService
{
    public function bootstrapProfile(World $world, array $preset): WorldPowerProfile
    {
        $schemaKey = $preset['key'] ?? $world->config['preset_key'] ?? 'cuu_trong_thien';
        $schemas = config('power_schemas');
        $schema = $schemas[$schemaKey] ?? $this->inferSchemaFromPowerSystem($preset, $schemas);

        $profile = WorldPowerProfile::updateOrCreate(
            [
                'world_id' => $world->id,
                'schema_key' => $schemaKey,
            ],
            [
                'parameters' => [
                    'power_system' => $schema['power_system'] ?? ($preset['power_system'] ?? 'NONE'),
                    'power_ceiling' => $preset['power_ceiling'] ?? null,
                    'tech_level' => $preset['tech_level'] ?? null,
                    'social_structure' => $preset['social_structure'] ?? null,
                    'archetype' => $preset['archetype'] ?? null,
                    'seed_vector' => $preset['seed_vector'] ?? null,
                    'drift_profile' => $preset['drift_profile'] ?? null,
                ],
                'material_affinities' => Arr::get($schema, 'resources.material_tags', []),
                'progression_state' => [
                    'current_stage' => $world->config['current_stage'] ?? 'mundane',
                    'pressure' => 0,
                    'stage_history' => [],
                ],
                'collision_traits' => [
                    'genre' => $preset['genre'] ?? $world->genre,
                    'author_persona' => $preset['author_persona'] ?? null,
                ],
            ]
        );

        return $profile;
    }

    public function resolveSchema(string $schemaKey): ?array
    {
        $schemas = config('power_schemas');
        return $schemas[$schemaKey] ?? null;
    }

    public function getProfile(World $world): ?WorldPowerProfile
    {
        return $world->powerProfile;
    }

    private function inferSchemaFromPowerSystem(array $preset, array $schemas): array
    {
        $powerSystem = $preset['power_system'] ?? 'NONE';

        foreach ($schemas as $schema) {
            if (($schema['power_system'] ?? null) === $powerSystem) {
                return $schema;
            }
        }

        $key = Str::slug($preset['key'] ?? 'generic', '_');

        return [
            'label' => $preset['name'] ?? Str::title(str_replace('_', ' ', $key)),
            'preset_key' => $preset['key'] ?? $key,
            'power_system' => $powerSystem,
            'paths' => [
                'body' => [],
                'energy' => [],
                'spirit' => [],
            ],
            'resources' => [
                'material_tags' => [],
                'currencies' => [],
            ],
            'narrative' => [
                'tone' => $preset['genre'] ?? 'generic',
                'keywords' => [],
            ],
        ];
    }
}
