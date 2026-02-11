<?php

namespace App\Narrative\Validation;

use App\Narrative\Values\NarrativeContext;
use App\Narrative\Values\ValidationResult;

class NarrativeValidationPipeline
{
    /** @var NarrativeValidator[] */
    private array $validators = [];

    public function addValidator(NarrativeValidator $validator): self
    {
        $this->validators[] = $validator;
        return $this;
    }

    public function run(string $draft, NarrativeContext $context): ValidationResult
    {
        $allViolations = [];
        $isRepairable = true;

        foreach ($this->validators as $validator) {
            $result = $validator->validate($draft, $context);

            if (!$result->valid) {
                $allViolations = array_merge($allViolations, $result->violations);
                if (!$result->repairable) {
                    $isRepairable = false;
                }
            }
        }

        if (!empty($allViolations)) {
            return ValidationResult::fail($allViolations, $isRepairable);
        }

        return ValidationResult::pass();
    }
}
