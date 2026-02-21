<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;
use Tuzy\Domain\Social\ValueObject\AddressingScope;
use Tuzy\Domain\Social\Enums\SocialStatus;

class HonorificConstraint implements NarrativeConstraint
{
    public function check(NarrativeContext $ctx, string $text): ConstraintResult
    {
        // 1. Extract pronouns/honorifics (simplified regex for Hán-Việt common terms)
        $pattern = '/\b(anh|tôi|tao|mày|ông|bà|chị|em|bản tọa|tại hạ|đạo hữu|ngươi|tiền bối|hậu bối)\b/ui';
        preg_match_all($pattern, $text, $matches);
        $found = array_unique($matches[0]);

        foreach ($found as $h) {
            // Check against basic Vietnamese modern pronouns which are banned in Hán-Việt tone
            if (in_array(mb_strtolower($h), ['anh', 'tôi', 'tao', 'mày', 'ông', 'bà', 'chị', 'em'])) {
                return ConstraintResult::fail("Prohibited modern honorific: '$h'");
            }

            // 2. Contextual Social Constraints
            // If in Public scope and addressing an Authority/Sovereign, 'ngươi' is a violation
            if ($ctx->socialContext?->scope === AddressingScope::PUBLIC) {
                if (mb_strtolower($h) === 'ngươi' && $ctx->socialContext?->speakerStatus != SocialStatus::SOVEREIGN) {
                     // Generally inappropriate to use 'ngươi' to a superior or peer in public
                     // This is a simplified check
                     return ConstraintResult::fail("Disrespectful honorific '$h' used in PUBLIC scope");
                }
            }
        }

        return ConstraintResult::pass();
    }
}
