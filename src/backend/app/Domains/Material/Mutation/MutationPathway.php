<?php

namespace App\Domains\Material\Mutation;

use Illuminate\Support\Facades\File;

class MutationPathway
{
    private array $pathways;

    public function __construct()
    {
        $path = app_path('Domains/Material/Mutation/Data/mutation_pathways.json');
        $json = File::get($path);
        $this->pathways = json_decode($json, true);
    }

    /**
     * Get all mutation pathways for a material code.
     */
    public function getPathways(string $materialCode): array
    {
        return $this->pathways[$materialCode] ?? [];
    }

    /**
     * Find matching pathway based on world context.
     */
    public function findMatchingPathway(string $materialCode, array $worldContext): ?array
    {
        $pathways = $this->getPathways($materialCode);

        foreach ($pathways as $pathway) {
            if ($this->evaluateTrigger($pathway['trigger_condition'], $worldContext)) {
                return $pathway;
            }
        }

        return null;
    }

    /**
     * Evaluate trigger condition against world context.
     * Simple implementation - in production would use expression parser.
     */
    private function evaluateTrigger(string $condition, array $context): bool
    {
        // Parse simple conditions like "sacred > 0.8" or "violence < 0.2"
        // For AND conditions: "change > 0.7 AND order > 0.5"
        
        $conditions = explode(' AND ', $condition);
        
        foreach ($conditions as $singleCondition) {
            if (!$this->evaluateSingleCondition(trim($singleCondition), $context)) {
                return false;
            }
        }
        
        return true;
    }

    private function evaluateSingleCondition(string $condition, array $context): bool
    {
        // Parse "key operator value"
        if (preg_match('/(\w+)\s*([<>]=?)\s*([\d.]+)/', $condition, $matches)) {
            $key = $matches[1];
            $operator = $matches[2];
            $threshold = (float)$matches[3];
            
            $value = $context[$key] ?? 0;
            
            return match($operator) {
                '>' => $value > $threshold,
                '>=' => $value >= $threshold,
                '<' => $value < $threshold,
                '<=' => $value <= $threshold,
                default => false
            };
        }
        
        return false;
    }
}
