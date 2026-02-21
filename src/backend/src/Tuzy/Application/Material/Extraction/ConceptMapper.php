<?php

namespace Tuzy\Application\Material\Extraction;

use Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface;

/**
 * ConceptMapper - Map extracted concepts to existing materials
 * 
 * Strategies:
 * 1. Exact code match
 * 2. Name similarity (Levenshtein)
 * 3. Ontology + function match
 */
class ConceptMapper
{
    public function __construct(
        private MaterialRepositoryInterface $repository
    ) {}

    /**
     * Map concepts to existing materials or create templates.
     * 
     * @param array $concepts Extracted concepts from Normalizer
     * @return array Mappings
     */
    public function map(array $concepts): array
    {
        $mappings = [];
        
        foreach ($concepts as $concept) {
            $existingMaterial = $this->findSimilarMaterial($concept);
            
            if ($existingMaterial) {
                $mappings[] = [
                    'concept' => $concept,
                    'material' => $existingMaterial,
                    'match_type' => 'existing',
                    'similarity' => $this->calculateSimilarity($concept, $existingMaterial),
                ];
            } else {
                $mappings[] = [
                    'concept' => $concept,
                    'template' => $this->createTemplate($concept),
                    'match_type' => 'new',
                ];
            }
        }
        
        return $mappings;
    }

    /**
     * Find similar existing material.
     */
    private function findSimilarMaterial(array $concept): ?array
    {
        $allMaterials = $this->repository->getAllMaterials();
        
        // Strategy 1: Exact code match
        $exactMatch = $allMaterials->firstWhere('code', $concept['code'] ?? null);
        if ($exactMatch) {
            return $exactMatch->toArray();
        }

        // Strategy 2: Name similarity
        $bestMatch = null;
        $bestSimilarity = 0.0;

        foreach ($allMaterials as $material) {
            $similarity = $this->calculateNameSimilarity(
                $concept['name'] ?? '',
                $material->name
            );

            if ($similarity > $bestSimilarity && $similarity > 0.7) {
                $bestSimilarity = $similarity;
                $bestMatch = $material;
            }
        }

        if ($bestMatch) {
            return $bestMatch->toArray();
        }

        // Strategy 3: Ontology + function match
        $ontologyMatch = $allMaterials->first(function ($material) use ($concept) {
            return strtolower($material->ontology->value) === strtolower($concept['ontology'] ?? '') &&
                   strtolower($material->function->value) === strtolower($concept['function'] ?? '');
        });

        return $ontologyMatch?->toArray();
    }

    /**
     * Calculate name similarity using Levenshtein distance.
     */
    private function calculateNameSimilarity(string $name1, string $name2): float
    {
        $name1 = strtolower(trim($name1));
        $name2 = strtolower(trim($name2));

        if ($name1 === $name2) {
            return 1.0;
        }

        $maxLen = max(strlen($name1), strlen($name2));
        if ($maxLen === 0) {
            return 0.0;
        }

        $distance = levenshtein($name1, $name2);
        return 1.0 - ($distance / $maxLen);
    }

    /**
     * Calculate overall similarity between concept and material.
     */
    private function calculateSimilarity(array $concept, array $material): float
    {
        $nameSim = $this->calculateNameSimilarity(
            $concept['name'] ?? '',
            $material['name'] ?? ''
        );

        $ontologySim = (strtolower($concept['ontology'] ?? '') === strtolower($material['ontology'] ?? '')) ? 1.0 : 0.0;
        $functionSim = (strtolower($concept['function'] ?? '') === strtolower($material['function'] ?? '')) ? 1.0 : 0.0;

        // Weighted average
        return ($nameSim * 0.5) + ($ontologySim * 0.3) + ($functionSim * 0.2);
    }

    /**
     * Create material template from concept.
     */
    private function createTemplate(array $concept): array
    {
        return [
            'code' => $concept['code'] ?? strtoupper(str_replace(' ', '_', $concept['name'] ?? 'UNKNOWN')),
            'name' => $concept['name'] ?? 'Unknown Material',
            'ontology' => $concept['ontology'] ?? 'behavioral',
            'function' => $concept['function'] ?? 'destabilizing',
            'description' => $concept['evidence'] ?? '',
            'pressure_outputs' => $concept['suggested_outputs'] ?? [],
            'decay_rate' => $concept['decay_rate'] ?? 1.0,
            'legacy_outputs' => $concept['legacy_outputs'] ?? [
                'type' => 'historical_trace',
                'strength' => $concept['strength'] ?? 0.5,
            ],
            'preconditions' => $concept['preconditions'] ?? [],
            'incompatible_with' => $concept['incompatible_with'] ?? [],
        ];
    }
}
