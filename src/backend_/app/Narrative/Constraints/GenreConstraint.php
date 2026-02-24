<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;

class GenreConstraint implements NarrativeConstraint
{
    public function check(NarrativeContext $ctx, string $text): ConstraintResult
    {
        if ($ctx->genre === 'survival') {
            // 1. Sentence Length check (Survival prefers short, punchy sentences)
            $sentences = preg_split('/[.!?]/', $text, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sentences as $sentence) {
                if (str_word_count(trim($sentence)) > 40) {
                    return ConstraintResult::fail("Survival genre violation: Sentence too long and flowery.");
                }
            }

            // 2. Emotional Modifier check
            $prohibitedModifiers = ['tuyệt đẹp', 'lộng lẫy', 'huy hoàng', 'tráng lệ'];
            foreach ($prohibitedModifiers as $mod) {
                if (mb_stripos($text, $mod) !== false) {
                    return ConstraintResult::fail("Survival genre violation: Over-emotive modifier '$mod' detected.");
                }
            }
        }

        return ConstraintResult::pass();
    }
}
