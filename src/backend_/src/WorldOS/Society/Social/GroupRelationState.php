<?php

namespace WorldOS\Society\Social;

use WorldOS\Society\Social\Enums\RelationshipTone;

class GroupRelationState
{
    public function __construct(
        public readonly string $speakerId,
        public readonly string $groupId,
        
        // Group relations are simpler: Respect vs Hostility
        public int $respect = 0,
        public int $hostility = 0,
        
        public RelationshipTone $tone = RelationshipTone::NEUTRAL
    ) {}
}
