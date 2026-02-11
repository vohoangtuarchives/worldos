<?php

namespace App\Narrative\Validation;

use App\Narrative\Values\NarrativeContext;
use App\Narrative\Values\ValidationResult;

class HonorificValidator implements NarrativeValidator
{
    private array $forbiddenPronouns = [
        'tôi', 'tao', 'mày',
        'anh', 'chị', 'em',
        'ông', 'bà',
        'nó', 'hắn' // 'hắn' might be allowed in some contexts, but let's strictly follow the user request for now
    ];

    public function validate(string $text, NarrativeContext $context): ValidationResult
    {
        // Only valid for Vietnamese target
        if ($context->targetLanguage !== 'vi') {
            return ValidationResult::pass();
        }

        // Only valid for Hán-Việt tone
        if ($context->tone !== 'han-viet') {
            return ValidationResult::pass();
        }

        $violations = $this->scanHonorifics($text);

        return empty($violations)
            ? ValidationResult::pass()
            : ValidationResult::fail($violations, true);
    }

    private function scanHonorifics(string $text): array
    {
        $violations = [];

        foreach ($this->forbiddenPronouns as $term) {
            // Regex to match whole words, case insensitive, unicode support
            if (preg_match('/\b' . mb_strtolower($term, 'UTF-8') . '\b/u', mb_strtolower($text, 'UTF-8'))) {
                $violations[] = "Non Han-Viet honorific: {$term}";
            }
        }

        return array_unique($violations);
    }
}
