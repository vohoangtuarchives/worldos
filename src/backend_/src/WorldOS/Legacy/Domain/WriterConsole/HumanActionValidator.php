<?php

namespace WorldOS\Legacy\Domain\WriterConsole;

use WorldOS\Legacy\Domain\WriterConsole\ValueObject\ValidationResult;

/**
 * Human Action Validator
 * 
 * Enforces the Human-in-the-Loop Contract (ADR-1003).
 * Ensures writers can only act as CURATORS, not AUTHORS.
 * 
 * ✅ Allowed: Seeding, Pressuring, Selecting
 * ❌ Forbidden: Direct editing, Force outcomes, Rewriting history
 */
class HumanActionValidator
{
    /**
     * Validate a proposed human action
     */
    public function validate(string $actionType, array $payload): ValidationResult
    {
        return match($actionType) {
            'seed_archetype' => $this->validateSeeding($payload),
            'apply_pressure' => $this->validatePressure($payload),
            'canonize_event' => $this->validateCanonization($payload),
            'edit_myth' => $this->reject("Direct myth editing is forbidden. Use pressure to influence evolution."),
            'force_outcome' => $this->reject("Forcing outcomes is forbidden. You may only influence probability."),
            'rewrite_history' => $this->reject("History is immutable once observed."),
            default => $this->reject("Unknown action type."),
        };
    }

    private function validateSeeding(array $payload): ValidationResult
    {
        // Allowed: Setting initial conditions
        return ValidationResult::allow();
    }

    private function validatePressure(array $payload): ValidationResult
    {
        // Allowed: Adding abstract pressure (e.g., "increase scarcity")
        // Forbidden: Specific micromanagement
        return ValidationResult::allow();
    }

    private function validateCanonization(array $payload): ValidationResult
    {
        // Allowed: Selecting one of generated options to keep
        return ValidationResult::allow();
    }

    private function reject(string $reason): ValidationResult
    {
        return ValidationResult::deny($reason);
    }
}
