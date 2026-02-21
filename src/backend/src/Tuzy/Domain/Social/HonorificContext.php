<?php

namespace Tuzy\Domain\Social;

use Tuzy\Domain\Social\Enums\RelativeAgeRank;
use Tuzy\Domain\Social\Enums\SocialStatus;
use Tuzy\Domain\Social\Enums\SituationType;
use Tuzy\Domain\Social\ValueObject\AddressingScope;

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
