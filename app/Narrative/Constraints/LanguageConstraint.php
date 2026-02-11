<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;

class LanguageConstraint implements NarrativeConstraint
{
    private array $bannedPatterns = [
        '/\b(level|hp|mp|exp|skill|buff|debuff|quest|system|ui|interface|dashboard)\b/i',
        '/\b(vãi|đắng lòng|chanh sả|bó tay|quẩy)\b/i', // Vietnamese modern slang
    ];

    public function check(NarrativeContext $ctx, string $text): ConstraintResult
    {
        foreach ($this->bannedPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return ConstraintResult::fail("Modern/Game language detected: '{$matches[0]}'");
            }
        }

        // Check for English characters in blocks (heuristic for tech jargon)
        if (preg_match('/[a-zA-Z]{10,}/', $text, $matches)) {
             // Too long English word usually means a tech term or error
             return ConstraintResult::fail("Potential technical jargon detected: '{$matches[0]}'");
        }

        return ConstraintResult::pass();
    }
}
