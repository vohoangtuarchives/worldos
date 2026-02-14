<?php

namespace App\Domains\Material\Extraction;

use App\Domains\Material\Enums\MaterialOntology;
use App\Domains\Material\Enums\MaterialFunction;

/**
 * MaterialValidator - Enforce material laws
 * 
 * Validation Rules:
 * 1. Must have decay definition
 * 2. Must have legacy definition
 * 3. Ontology + Function must be valid
 * 4. Pressure outputs must be normalized
 * 5. Incompatibilities must reference existing materials
 */
class MaterialValidator
{
    /**
     * Validate material template.
     * 
     * @param array $template Material template
     * @return ValidationResult
     */
    public function validate(array $template): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Rule 1: Decay
        if (empty($template['decay_rate']) && empty($template['decay_conditions'])) {
            $errors[] = 'Missing decay definition (decay_rate or decay_conditions required)';
        }

        // Rule 2: Legacy
        if (empty($template['legacy_outputs'])) {
            $errors[] = 'Missing legacy definition (legacy_outputs required)';
        }

        // Rule 3: Ontology + Function
        if (!$this->isValidOntology($template['ontology'] ?? null)) {
            $errors[] = 'Invalid ontology: ' . ($template['ontology'] ?? 'null');
        }

        if (!$this->isValidFunction($template['function'] ?? null)) {
            $errors[] = 'Invalid function: ' . ($template['function'] ?? 'null');
        }

        // Rule 4: Pressure outputs
        if (isset($template['pressure_outputs'])) {
            foreach ($template['pressure_outputs'] as $key => $value) {
                if (!is_numeric($value)) {
                    $errors[] = "Pressure output '{$key}' must be numeric";
                } elseif ($value < -1.0 || $value > 1.0) {
                    $errors[] = "Pressure output '{$key}' must be in range [-1.0, 1.0]";
                }
            }
        } else {
            $warnings[] = 'No pressure outputs defined';
        }

        // Rule 5: Incompatibilities (warning only, can't validate without DB)
        if (isset($template['incompatible_with']) && !empty($template['incompatible_with'])) {
            $warnings[] = 'Incompatibilities defined - verify material codes exist';
        }

        // Additional validations
        if (empty($template['code'])) {
            $errors[] = 'Missing material code';
        }

        if (empty($template['name'])) {
            $errors[] = 'Missing material name';
        }

        return new ValidationResult(
            valid: empty($errors),
            errors: $errors,
            warnings: $warnings
        );
    }

    /**
     * Check if ontology is valid.
     */
    private function isValidOntology(?string $ontology): bool
    {
        if (!$ontology) {
            return false;
        }

        return in_array(strtolower($ontology), [
            'institutional',
            'behavioral',
            'symbolic',
        ]);
    }

    /**
     * Check if function is valid.
     */
    private function isValidFunction(?string $function): bool
    {
        if (!$function) {
            return false;
        }

        return in_array(strtolower($function), [
            'stabilizing',
            'destabilizing',
            'transformative',
        ]);
    }
}

/**
 * ValidationResult - Result of material validation
 */
class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors = [],
        public readonly array $warnings = []
    ) {}

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
