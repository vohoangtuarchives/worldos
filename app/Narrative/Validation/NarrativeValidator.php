<?php

namespace App\Narrative\Validation;

use App\Narrative\Values\NarrativeContext;
use App\Narrative\Values\ValidationResult;

interface NarrativeValidator
{
    /**
     * Validate the narrative text against specific rules.
     *
     * @param string $text The raw narrative draft
     * @param NarrativeContext $context The context describing strictness/language
     * @return ValidationResult
     */
    public function validate(string $text, NarrativeContext $context): ValidationResult;
}
