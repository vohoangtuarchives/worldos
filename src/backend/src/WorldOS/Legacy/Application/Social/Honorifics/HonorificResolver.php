<?php

namespace WorldOS\Legacy\Application\Social\Honorifics;

use WorldOS\Society\Social\HonorificContext;
use WorldOS\Society\Social\Enums\RelativeAgeRank;
use WorldOS\Society\Social\Enums\SocialStatus;
use WorldOS\Society\Social\Enums\RelationshipTone;
use WorldOS\Society\Social\Enums\SituationType;

class HonorificResolver
{
    public function resolveSelf(HonorificContext $ctx): string
    {
        // 1. Context Overrides (Rituals, Specific Situations)
        if ($ctx->situation === SituationType::RITUAL) {
            return 'tại hạ';
        }

        // 2. Status Dominance
        if ($ctx->speakerStatus === SocialStatus::SOVEREIGN) {
            return 'bản tọa';
        }

        // 3. Age/Experience
        return match ($ctx->speakerAge) {
            RelativeAgeRank::YOUTH => 'tiểu sinh', // Or 'ta' if arrogant
            RelativeAgeRank::JUNIOR => 'tiểu bối', // Humble
            RelativeAgeRank::MATURE => 'tại hạ',   // Standard
            RelativeAgeRank::SENIOR => 'lão phu',
            RelativeAgeRank::ANCIENT => 'lão quái', // Or 'cổ giả'
            default => 'tại hạ',
        };
    }

    public function resolveTarget(HonorificContext $ctx): string
    {
        // PRIORITY 1: Observer Effect (Public Floor)
        if ($ctx->hasObservers) {
            $base = $this->resolvePrivate($ctx);
            // Sanitize: If base is 'ngươi' (hostile) or too informal, elevate it
            if ($base === 'ngươi') {
                return 'các hạ'; // Cold but polite in public
            }
            if ($base === 'tiểu sinh') {
                return 'tại hạ'; // More formal
            }
            return $base;
        }

        return $this->resolvePrivate($ctx);
    }

    private function resolvePrivate(HonorificContext $ctx): string
    {
        // PRIORITY 1: Individual Directed Relation
        if ($ctx->relation) {
            // Extreme Hostility overrides everything
            if ($ctx->relation->tone === RelationshipTone::HOSTILE || $ctx->relation->hostility > 50) {
                return 'ngươi';
            }

            // High Respect
            if ($ctx->relation->tone === RelationshipTone::RESPECT || $ctx->relation->respect > 80) {
                return 'tôn giả'; 
            }

            // Intimacy
            if ($ctx->relation->tone === RelationshipTone::INTIMATE) {
                return 'huynh đài'; 
            }
        }

        // PRIORITY 2: Group Relation (Target's Group)
        if ($ctx->groupRelation) {
            if ($ctx->groupRelation->tone === RelationshipTone::HOSTILE || $ctx->groupRelation->hostility > 50) {
                return 'các hạ'; // Cold, distant
            }
            if ($ctx->groupRelation->respect > 70) {
                return 'tiền bối'; 
            }
        }

        // PRIORITY 3: Situation Overrides
        if ($ctx->situation === SituationType::COMBAT) {
            return 'ngươi';
        }

        // PRIORITY 4: Default Social/Age Rules
        return 'đạo hữu';
    }
}
