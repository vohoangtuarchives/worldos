<?php

namespace App\Domains\Social;

use WorldOS\Society\Social\Enums\RelativeAgeRank;
use WorldOS\Society\Social\Enums\SocialStatus;
use WorldOS\Society\Social\Enums\SituationType;
use WorldOS\Society\Social\ValueObject\AddressingScope;

class HonorificContext
{
    public function __construct(
        // Speaker Attributes
        public readonly RelativeAgeRank $speakerAge,
        public readonly SocialStatus $speakerStatus,

        // Interaction Context
        public readonly SituationType $situation,
        public readonly AddressingScope $scope = AddressingScope::PUBLIC,
        public readonly bool $hasObservers = false,
        
        // Relationship State (Directed: Speaker -> Target)
        public readonly ?DirectedSocialRelation $relation = null,
        
        // Group Relationship State (Speaker -> Target's Group)
        public readonly ?GroupRelationState $groupRelation = null
    ) {}
}
