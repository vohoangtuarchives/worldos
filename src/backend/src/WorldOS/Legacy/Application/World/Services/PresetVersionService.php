<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World\OntologyNode;
use App\Models\World\PresetMaterial;
use App\Models\World\PresetVersion;
use App\Models\World\WorldPreset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresetVersionService
{
    public function createInitialVersion(WorldPreset $preset): PresetVersion
    {
        return PresetVersion::create([
            'preset_id' => $preset->id,
            'version_label' => 'v1',
            'status' => 'active',
            'power_policy' => $preset->power_policy,
            'resource_policy' => $preset->resource_policy,
            'conflict_policy' => $preset->conflict_policy,
            'escalation_policy' => $preset->escalation_policy,
            'myth_policy' => $preset->myth_policy,
            'scar_policy' => $preset->scar_policy,
            'config' => $preset->config,
        ]);
    }

    public function cloneVersion(PresetVersion $source, string $newLabel): PresetVersion
    {
        return DB::transaction(function () use ($source, $newLabel) {
            // 1. Clone Version
            $newVersion = $source->replicate();
            $newVersion->id = Str::uuid()->toString();
            $newVersion->version_label = $newLabel;
            $newVersion->parent_version_id = $source->id;
            $newVersion->status = 'draft';
            $newVersion->push();

            // 2. Clone Ontology (Preserving Hierarchy and Mapping)
            $nodeMap = $this->cloneOntology($source, $newVersion);

            // 3. Clone Materials & Attach to new Ontology Nodes
            $this->cloneMaterials($source, $newVersion, $nodeMap);

            return $newVersion;
        });
    }

    private function cloneOntology(PresetVersion $source, PresetVersion $target): array
    {
        $nodeMap = []; // old_id => new_id
        $nodes = $source->ontologyNodes()->orderBy('depth')->get();

        foreach ($nodes as $node) {
            $newNode = $node->replicate();
            $newNode->id = Str::uuid()->toString();
            $newNode->preset_version_id = $target->id;
            $newNode->parent_id = $node->parent_id ? ($nodeMap[$node->parent_id] ?? null) : null;
            $newNode->push();

            $nodeMap[$node->id] = $newNode->id;
        }

        return $nodeMap;
    }

    private function cloneMaterials(PresetVersion $source, PresetVersion $target, array $nodeMap): void
    {
        $materials = $source->materials()->with('tags')->get();

        foreach ($materials as $material) {
            $newMaterial = $material->replicate();
            $newMaterial->id = Str::uuid()->toString();
            $newMaterial->preset_version_id = $target->id;
            $newMaterial->push();

            // Re-attach tags using the new ontology node IDs
            $newTagIds = $material->tags->map(function ($tag) use ($nodeMap) {
                return $nodeMap[$tag->id] ?? null;
            })->filter()->toArray();

            $newMaterial->tags()->sync($newTagIds);
        }
    }
}
