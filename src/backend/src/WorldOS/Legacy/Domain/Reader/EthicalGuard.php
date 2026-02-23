<?php

namespace WorldOS\Legacy\Domain\Reader;

/**
 * EthicalGuard - Enforce ethical boundaries (ADR-0008 compliance)
 * 
 * Prevents harmful reader influence.
 */
class EthicalGuard
{
    /**
     * Maximum influence per epoch.
     */
    private const MAX_INFLUENCE = 0.3;

    /**
     * Banned keywords (harmful content).
     */
    private const BANNED_KEYWORDS = [
        'genocide',
        'massacre',
        'exterminate',
        'enslave',
        'torture',
        'ethnic cleansing',
    ];

    /**
     * Validate choice against ethical rules.
     * 
     * @param array $choice Choice to validate
     * @return array Validation result
     */
    public function validateChoice(array $choice): array
    {
        $errors = [];
        $warnings = [];

        // Rule 1: No harmful content
        if ($this->containsHarmfulContent($choice)) {
            $errors[] = 'Choice contains harmful or unethical content';
        }

        // Rule 2: Influence within bounds
        foreach ($choice['options'] as $option) {
            foreach ($option['delta'] ?? [] as $field => $value) {
                if (abs($value) > self::MAX_INFLUENCE) {
                    $errors[] = "Delta for '{$field}' exceeds limit: {$value} (max: ±" . self::MAX_INFLUENCE . ")";
                }
            }
        }

        // Rule 3: Must have at least 2 options
        if (count($choice['options']) < 2) {
            $errors[] = 'Choice must have at least 2 options';
        }

        // Warning: Very high impact
        foreach ($choice['options'] as $option) {
            $totalImpact = array_sum(array_map('abs', $option['delta'] ?? []));
            if ($totalImpact > 0.5) {
                $warnings[] = "Option '{$option['id']}' has very high total impact: {$totalImpact}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Check for harmful content in choice.
     */
    private function containsHarmfulContent(array $choice): bool
    {
        $text = strtolower(json_encode($choice));

        foreach (self::BANNED_KEYWORDS as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate delta magnitude.
     */
    public function validateDelta(array $delta): array
    {
        $errors = [];

        foreach ($delta as $field => $value) {
            if (abs($value) > self::MAX_INFLUENCE) {
                $errors[] = "Delta for '{$field}' exceeds limit: {$value}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
