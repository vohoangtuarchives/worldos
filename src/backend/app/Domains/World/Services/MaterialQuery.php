<?php

namespace App\Domains\World\Services;

use App\Models\World\PresetVersion;
use App\Models\World\PresetMaterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MaterialQuery
{
    private Builder $query;

    public function __construct(PresetVersion $version)
    {
        $this->query = PresetMaterial::query()
            ->where('preset_version_id', $version->id);
    }

    public static function for(PresetVersion $version): self
    {
        return new self($version);
    }

    public function under(string $ontologyPath): self
    {
        // Filter materials that have AT LEAST ONE tag under this path
        $this->query->whereHas('tags', function (Builder $q) use ($ontologyPath) {
            $q->where('path', $ontologyPath)
              ->orWhere('path', 'like', $ontologyPath . '.%');
        });

        return $this;
    }

    public function type(string $type): self
    {
        $this->query->where('type', $type);
        return $this;
    }

    public function minRarity(string $rarity): self
    {
        // Naive rarity check - assumes string comparison or enum matching needed in real app
        // For vertical slice, explicit match
        $this->query->where('rarity', $rarity);
        return $this;
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function withOverrides(string $worldId): Collection
    {
        $baseMaterials = $this->query->get();
        
        // Fetch overrides
        $overrides = \App\Models\World\WorldMaterialOverride::where('world_id', $worldId)->get();
        
        // map: preset_material_id => override
        $modificationMap = $overrides->whereNotNull('preset_material_id')->keyBy('preset_material_id');
        
        // extensions
        $extensions = $overrides->whereNull('preset_material_id');

        // Process Base Materials
        $finalMaterials = $baseMaterials->map(function ($material) use ($modificationMap) {
            if ($override = $modificationMap->get($material->id)) {
                if ($override->override_mode === 'disable') {
                    return null;
                }
                
                // Clone and Modify
                $material = clone $material;
                if ($override->name) $material->name = $override->name;
                if ($override->slug) $material->slug = $override->slug;
                if ($override->power_scale_modifier) $material->power_scale *= $override->power_scale_modifier;
                if ($override->metadata) $material->metadata = array_merge($material->metadata ?? [], $override->metadata);
                // Rarity override etc.
                
                return $material;
            }
            return $material;
        })->filter();

        // Process Extensions
        foreach ($extensions as $ext) {
            // Create a pseudo-PresetMaterial from the override
            $newMaterial = new PresetMaterial([
                'id' => $ext->id, // Use override ID or generate new?
                'slug' => $ext->slug,
                'name' => $ext->name,
                'type' => 'extension', // or from metadata
                'metadata' => $ext->metadata,
                'power_scale' => $ext->power_scale_modifier ?? 0, // In extension mode, this is base power
                'rarity' => $ext->rarity_override ?? 'common',
            ]);
            // extensions might not have tags unless we link them to ontology nodes?
            // For now, extensions are untagged or we need a way to tag them.
            // Assumption for Vertical Slice: Extensions are just added to the pool.
            $finalMaterials->push($newMaterial);
        }

        return $finalMaterials;
    }

    public function random(): ?PresetMaterial
    {
        return $this->query->inRandomOrder()->first();
    }
}
