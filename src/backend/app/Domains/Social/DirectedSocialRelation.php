<?php

namespace App\Domains\Social;

use WorldOS\Society\Social\Enums\RelationshipTone;

class DirectedSocialRelation
{
    public function __construct(
        public readonly string $fromId, // Character ID
        public readonly string $toId,   // Character ID
        
        // Dynamic axes (integer scales 0-100 normally, simplified here)
        public int $respect = 0,      // 0 = None, 100 = Worship
        public int $familiarity = 0,  // 0 = Stranger, 100 = Soulmate
        public int $hostility = 0,    // 0 = Peace, 100 = Mortal Enemy
        
        // Calculated Tone (derived from above, but can be cached)
        public RelationshipTone $tone = RelationshipTone::NEUTRAL
    ) {}

    // Methods to derive tone from stats will be in Logic layer
}
