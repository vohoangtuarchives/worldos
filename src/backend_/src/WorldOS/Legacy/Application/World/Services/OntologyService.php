<?php

namespace WorldOS\Legacy\Application\World\Services;

use App\Models\World\OntologyNode;
use App\Models\World\PresetVersion;
use Illuminate\Support\Str;

class OntologyService
{
    public function createNode(PresetVersion $version, string $name, string $slug, ?OntologyNode $parent = null): OntologyNode
    {
        $path = $parent ? $parent->path . '.' . $slug : $slug;
        $depth = $parent ? $parent->depth + 1 : 0;

        return OntologyNode::create([
            'id' => Str::uuid()->toString(),
            'preset_version_id' => $version->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
            'path' => $path,
            'depth' => $depth,
        ]);
    }

    public function findNodeByPath(PresetVersion $version, string $path): ?OntologyNode
    {
        return $version->ontologyNodes()->where('path', $path)->first();
    }
}
