<?php

namespace App\StoryEngine\Services;

use App\Models\Story;
use App\Models\World;
use App\Models\WorldPowerProfile;
use App\StoryEngine\Seed;
use App\Domains\World\Services\WorldEventLedger;

class NarrativeAssembler
{
    public function assemble(Story $story, array $generatedContent, Seed $seed): array
    {
        $world = $story->world;
        $profile = $world->powerProfile;
        $schema = $profile ? config('power_schemas')[$profile->schema_key] ?? null : null;

        $loreCapsule = $this->buildLoreCapsule($schema);
        $socialDigest = $this->extractSocialDigest($world);
        $materialAppendix = $this->buildMaterialAppendix($profile);         

        return array_filter([
            'chapter' => $generatedContent['content'] ?? '',
            'seed' => [ 
                'type' => $seed->type, 
                'dimension' => $seed->dimension, 
                'severity' => $seed->severity 
            ],
            'lore' => $loreCapsule,
            'society' => $socialDigest,
            'materials' => $materialAppendix,
        ]);
    }

    private function buildLoreCapsule(?array $schema): ?array
    {
        if (!$schema) return null;

        return [
            'label' => $schema['label'] ?? null,
            'power_system' => $schema['power_system'] ?? null,
            'paths' => $schema['paths'] ?? [],
            'keywords' => $schema['narrative']['keywords'] ?? [],
        ];
    }

    private function extractSocialDigest(?World $world): ?array
    {
        if (!$world) return null;

        $ledger = app(WorldEventLedger::class);
        $recent = $ledger->getRecentSocialEvents($world, 5);

        return $recent ? ['events' => $recent] : null;
    }

    private function buildMaterialAppendix(?WorldPowerProfile $profile): ?array
    {
        if (!$profile) return null;

        $materials = $profile->material_affinities;
        if (empty($materials)) return null;

        return ['items' => $materials];
    }
}
