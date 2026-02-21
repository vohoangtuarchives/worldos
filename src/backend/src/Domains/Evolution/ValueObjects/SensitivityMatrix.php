<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\ValueObjects;

/**
 * SensitivityMatrix (Value Object)
 * 
 * Defines the coupling coefficients between civilizational dimensions.
 * Matrix[TargetDim][SourceDim] = weight
 */
class SensitivityMatrix
{
    private function __construct(
        public readonly array $matrix
    ) {}

    public static function default(): self
    {
        // 10 Dimensions: ce, sc, tech, stab, p, mp, legit, ecohesion, ineq, ie
        // Matrix[Target][Source] = weight
        $m = [
            'ce' => ['ce' => 0.05, 'stab' => 0.3, 'ie' => -0.2],
            'sc' => ['sc' => -0.05, 'ineq' => -0.6, 'legit' => 0.4, 'ie' => -0.5],
            'tech' => ['tech' => -0.02, 'ce' => 0.6, 'stab' => 0.5, 'prosperity' => 0.4, 'ie' => -0.4],
            'stability' => ['stability' => -0.01, 'legit' => 0.7, 'ineq' => -0.9, 'ie' => -1.5, 'mp' => -0.4, 'ecohesion' => 0.4],
            'prosperity' => ['prosperity' => -0.02, 'tech' => 0.9, 'stab' => 0.5, 'ie' => -0.7, 'mp' => -0.3],
            'militaryPressure' => ['militaryPressure' => 0.05, 'ie' => 0.6, 'ineq' => 0.4, 'legit' => -0.5],
            'legitimacy' => ['legitimacy' => -0.05, 'prosperity' => 0.4, 'ineq' => -1.2, 'tech' => -0.3, 'ie' => -0.5, 'ecohesion' => 0.5],
            'eliteCohesion' => ['eliteCohesion' => -0.03, 'legit' => 0.5, 'ineq' => -0.7, 'ie' => -0.6, 'stability' => 0.3],
            'inequality' => ['inequality' => 0.05, 'tech' => 0.5, 'legit' => -0.6, 'stability' => -0.4, 'prosperity' => 0.4, 'mobility' => -0.5],
            'internalEntropy' => ['internalEntropy' => 0.02, 'ineq' => 1.0, 'legit' => -0.8, 'prosperity' => 0.3, 'tech' => 0.2, 'stability' => -0.5, 'sustainability' => -0.4],
            'sustainability' => ['sustainability' => -0.05, 'tech' => -0.8, 'prosperity' => -0.4, 'ce' => 0.3],
            'mystery' => ['mystery' => -0.02, 'tech' => -0.5, 'ce' => 0.4, 'sc' => 0.3],
            'legacy' => ['legacy' => 0.01, 'ce' => 0.4, 'stab' => 0.2],
            'expansion' => ['expansion' => 0.05, 'prosperity' => 0.6, 'mp' => 0.4, 'stab' => -0.3],
            'info' => ['info' => 0.02, 'tech' => 0.7, 'ce' => 0.3, 'ie' => -0.2],
            'mobility' => ['mobility' => -0.05, 'info' => 0.6, 'p' => 0.3, 'ineq' => -0.8],
        ];

        return new self($m);
    }

    public function apply(array $forces, array $stateValues): array
    {
        $newForces = [];
        foreach ($forces as $dim => $baseForce) {
            $couplingModifier = 0.0;
            if (isset($this->matrix[$dim])) {
                foreach ($this->matrix[$dim] as $sourceDim => $weight) {
                    $sourceValue = $stateValues[$sourceDim] ?? 0.0;
                    
                    // Non-linear influence: influence is stronger if sourceValue is extreme
                    $influence = $weight * pow($sourceValue, 1.5);
                    $couplingModifier += $influence * 0.05; // Scaling factor for the matrix delta
                }
            }

            // Tech Ceiling: If Tech > 1.0, it feels heavy pressure from Prosperity/Stability
            if ($dim === 'tech' && ($stateValues['tech'] ?? 0) > 1.0) {
                $overExtension = $stateValues['tech'] - 1.0;
                $couplingModifier -= ($overExtension * 0.1); // Drag factor
            }

            $newForces[$dim] = $baseForce + $couplingModifier;
        }

        return $newForces;
    }
}
