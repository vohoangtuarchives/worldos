<?php

namespace App\Domains\Social;

use App\Domains\Social\Enums\RelativeAgeRank;
use App\Domains\Social\Enums\SocialStatus;
use App\Domains\Social\Enums\SituationType;

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
