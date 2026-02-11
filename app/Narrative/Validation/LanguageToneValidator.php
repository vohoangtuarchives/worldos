<?php

namespace App\Narrative\Validation;

use App\Narrative\Values\NarrativeContext;
use App\Narrative\Values\ValidationResult;

class LanguageToneValidator implements NarrativeValidator
{
    private array $forbiddenTerms = [
        'ui', 'panel', 'hệ thống', // System terms
        'level', 'lv.', 'cấp độ', 'exp', 'kinh nghiệm', // Game terms
        'hp', 'mp', 'thanh máu', 'năng lượng', 'mana',
        'skill', 'kỹ năng', 'chiêu thức', 'cool down', 'hồi chiêu',
        'buff', 'debuff', 'nâng cấp', 'thống kê', 'dữ liệu',
        'màn hình', 'giao diện', 'tích điểm', 'nhệm vụ', 'quest',
        'respawn', 'hồi sinh', 'check point', 'lưu game'
    ];

    public function validate(string $text, NarrativeContext $context): ValidationResult
    {
        // Tone check mainly for ensuring high-quality prose, not just Han-Viet
        // But let's apply it generally for the 'production' audience
        if ($context->audience !== 'human_reader') {
            return ValidationResult::pass();
        }

        $violations = [];

        foreach ($this->forbiddenTerms as $term) {
            if (mb_stripos($text, $term) !== false) {
                // Check if it's a false positive? 
                // For now, strict check.
                $violations[] = "Forbidden modern/game term: {$term}";
            }
        }

        return empty($violations)
            ? ValidationResult::pass()
            : ValidationResult::fail($violations, true);
    }
}
