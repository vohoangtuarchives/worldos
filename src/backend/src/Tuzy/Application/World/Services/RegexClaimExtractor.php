<?php

namespace Tuzy\Application\World\Services;

use Tuzy\Domain\World\Contracts\ClaimExtractorInterface;
use Tuzy\Domain\World\ValueObject\Claim;

class RegexClaimExtractor implements ClaimExtractorInterface
{
    /**
     * Extract factual claims from narrative text using Regex and Keywords.
     * 
     * @param string $text
     * @return Claim[]
     */
    public function extract(string $text): array
    {
        $claims = [];
        $lower = strtolower($text);

        // 1. Detect Resurrection
        if (str_contains($lower, 'came back to life') || str_contains($lower, 'resurrected') || str_contains($lower, 'returned from the dead')) {
            $claims[] = new Claim('RESURRECTION', 10);
        }

        // 2. Detect Cultivation Breakthrough
        if (str_contains($lower, 'broke through to the') || str_contains($lower, 'ascended to') || str_contains($lower, 'formed their golden core')) {
            // Estimate magnitude based on keywords
            $mag = 1;
            if (str_contains($lower, 'immortal')) $mag = 9;
            elseif (str_contains($lower, 'nascent soul')) $mag = 5;
            elseif (str_contains($lower, 'golden core')) $mag = 3;
            
            $claims[] = new Claim('CULTIVATION_BREAKTHROUGH', $mag);
        }

        // 3. Detect Magic / Spells
        if (str_contains($lower, 'cast a spell') || str_contains($lower, 'mana surged') || str_contains($lower, 'fireball')) {
            $claims[] = new Claim('SPELL_CAST', 3, 'unknown');
        }

        // 4. Detect Divine Intervention
        if (str_contains($lower, 'heavens descended') || str_contains($lower, 'divine punishment') || str_contains($lower, 'tribulation lightning')) {
            $claims[] = new Claim('DIVINE_INTERVENTION', 8);
        }

        // 5. Detect Technology
        if (str_contains($lower, 'mecha') || str_contains($lower, 'robot') || str_contains($lower, 'spaceship') || str_contains($lower, 'laser')) {
             $claims[] = new Claim('HIGH_TECH_USAGE', 5);
        }

        return $claims;
    }
}
