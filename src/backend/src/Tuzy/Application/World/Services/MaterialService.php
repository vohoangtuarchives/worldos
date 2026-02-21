<?php

namespace Tuzy\Application\World\Services;

use App\Models\World\MaterialDraft;
use App\Models\World\PresetMaterial;
use App\Models\World\PresetVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaterialService
{
    public function __construct(
        private PresetVersionService $versionService,
        private OntologyService $ontologyService
    ) {}

    public function createMaterial(PresetVersion $version, array $data, array $ontologyPaths = []): PresetMaterial
    {
        return DB::transaction(function () use ($version, $data, $ontologyPaths) {
            $material = PresetMaterial::create([
                'id' => Str::uuid()->toString(),
                'preset_version_id' => $version->id,
                'type' => $data['type'],
                'slug' => $data['slug'],
                'name' => $data['name'],
                'metadata' => $data['metadata'] ?? [],
                'power_scale' => $data['power_scale'] ?? 0,
                'rarity' => $data['rarity'] ?? 'common',
            ]);

            if (!empty($ontologyPaths)) {
                $nodeIds = [];
                foreach ($ontologyPaths as $path) {
                    $node = $this->ontologyService->findNodeByPath($version, $path);
                    if ($node) {
                        $nodeIds[] = $node->id;
                    }
                }
                $material->tags()->sync($nodeIds);
            }

            return $material;
        });
    }

    public function approveDraft(MaterialDraft $draft): PresetVersion
    {
        return DB::transaction(function () use ($draft) {
            $currentVersion = $draft->version;
            
            // 1. Clone New Version
            // Determine next version label (naive implementation)
            // Assuming v1 -> v2. In real app, parse semantic version.
            $currentLabel = $currentVersion->version_label;
            $newLabel = 'v' . ((int)substr($currentLabel, 1) + 1);
            
            $newVersion = $this->versionService->cloneVersion($currentVersion, $newLabel);
            
            // 2. Insert Material into New Version
            $this->createMaterial(
                $newVersion,
                $draft->payload,
                $draft->payload['ontology_paths'] ?? []
            );

            // 3. Mark Draft Approved
            $draft->update(['status' => 'approved']);
            
            // 4. Activate New Version (Optional policy)
            $newVersion->update(['status' => 'active']);

            return $newVersion;
        });
    }
}
