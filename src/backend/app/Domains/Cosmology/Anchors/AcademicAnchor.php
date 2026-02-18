<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Anchors;

use App\Domains\Cosmology\Contracts\StructuralAnchorInterface;

final class AcademicAnchor implements StructuralAnchorInterface
{
    public function getKey(): string
    {
        return 'academic_system';
    }

    public function generateInstitutions(): array
    {
        return [
            'council' => ['role' => 'knowledge_hierarchy', 'weight' => 0.9],
            'academy' => ['role' => 'training', 'weight' => 0.8],
            'archive' => ['role' => 'memory', 'weight' => 0.6],
            'debate_hall' => ['role' => 'conflict_resolution', 'weight' => 0.5],
        ];
    }

    public function generateConflictTopology(): array
    {
        return [
            'primary' => 'theory_schism',
            'secondary' => ['succession', 'resource_access'],
            'actors' => ['factions_by_doctrine', 'masters', 'students'],
        ];
    }

    public function protagonistArchetypes(): array
    {
        return ['student', 'master', 'heretic', 'guardian'];
    }

    public function resourceFlowModel(): array
    {
        return [
            'primary_resource' => 'knowledge',
            'distribution' => 'hierarchical_by_rank',
            'scarcity_driver' => 'access_control',
        ];
    }
}
