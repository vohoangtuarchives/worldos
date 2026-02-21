<?php

namespace Tuzy\Application\World\Services;

use Tuzy\Domain\World\Exception\OntologyViolation;

class AIResponseValidator
{
    private PrimitiveGuard $guard;

    public function __construct(PrimitiveGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Validate AI response for ontology violations
     * 
     * @param array $aiOutput AI-generated content
     * @param string $version WFR version
     * @throws OntologyViolation
     */
    public function validate(array $aiOutput, string $version = '1.0.0'): void
    {
        // Check if AI introduced new ontology concepts
        if ($this->containsNewOntology($aiOutput)) {
            throw new OntologyViolation(
                'AI attempted to introduce new ontology. Only instances are allowed.'
            );
        }

        // Validate any primitive references in the output
        if (isset($aiOutput['primitive_refs'])) {
            $this->guard->validate($aiOutput['primitive_refs'], $version);
        }
    }

    /**
     * Detect if AI output contains new ontological concepts
     */
    protected function containsNewOntology(array $aiOutput): bool
    {
        // Forbidden keys that indicate new ontology
        $forbiddenKeys = [
            'new_governance_form',
            'new_value_system',
            'new_power_source',
            'new_being_type',
            'new_primitive',
            'define_',
            'create_type',
        ];

        foreach ($forbiddenKeys as $key) {
            if ($this->arrayHasKeyContaining($aiOutput, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively check if array has key containing substring
     */
    protected function arrayHasKeyContaining(array $array, string $substring): bool
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && str_contains(strtolower($key), strtolower($substring))) {
                return true;
            }

            if (is_array($value) && $this->arrayHasKeyContaining($value, $substring)) {
                return true;
            }
        }

        return false;
    }
}
