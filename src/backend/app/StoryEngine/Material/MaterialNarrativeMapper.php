<?php

namespace App\StoryEngine\Material;

use App\Domains\Material\MaterialInstance;
use Illuminate\Support\Facades\File;

class MaterialNarrativeMapper
{
    private array $templates;

    public function __construct()
    {
        $path = app_path('StoryEngine/Material/Templates/material_event_templates.json');
        $json = File::get($path);
        $this->templates = json_decode($json, true);
    }

    /**
     * Generate narrative event from material activation.
     */
    public function generateActivationEvent(MaterialInstance $instance): ?string
    {
        $materialCode = $instance->material->code;
        $templates = $this->templates['material_activation'][$materialCode] ?? null;

        if (!$templates) {
            return null;
        }

        return $templates[array_rand($templates)];
    }

    /**
     * Generate narrative event from material collapse.
     */
    public function generateCollapseEvent(string $materialCode): ?string
    {
        $templates = $this->templates['material_collapse'][$materialCode] ?? null;

        if (!$templates) {
            return "The {$materialCode} collapses and fades from the world.";
        }

        return $templates[array_rand($templates)];
    }

    /**
     * Generate narrative event from material mutation.
     */
    public function generateMutationEvent(string $fromCode, string $toCode): ?string
    {
        $key = "{$fromCode}->{$toCode}";
        $templates = $this->templates['material_mutation'][$key] ?? null;

        if (!$templates) {
            return "{$fromCode} transforms into {$toCode}.";
        }

        return $templates[array_rand($templates)];
    }

    /**
     * Generate narrative event from material conflict.
     */
    public function generateConflictEvent(string $material1Code, string $material2Code): ?string
    {
        $key1 = "{$material1Code}+{$material2Code}";
        $key2 = "{$material2Code}+{$material1Code}";

        $templates = $this->templates['material_conflict'][$key1] 
            ?? $this->templates['material_conflict'][$key2] 
            ?? null;

        if (!$templates) {
            return "{$material1Code} clashes with {$material2Code}.";
        }

        return $templates[array_rand($templates)];
    }

    /**
     * Detect material conflicts based on incompatibility.
     */
    public function detectConflicts(array $activeInstances): array
    {
        $conflicts = [];

        foreach ($activeInstances as $i => $instance1) {
            $incompatible = $instance1->material->incompatible_with ?? [];

            foreach ($activeInstances as $j => $instance2) {
                if ($i >= $j) continue; // Avoid duplicates

                if (in_array($instance2->material->code, $incompatible)) {
                    $conflicts[] = [
                        'material1' => $instance1->material->code,
                        'material2' => $instance2->material->code,
                        'narrative' => $this->generateConflictEvent(
                            $instance1->material->code,
                            $instance2->material->code
                        )
                    ];
                }
            }
        }

        return $conflicts;
    }
}
