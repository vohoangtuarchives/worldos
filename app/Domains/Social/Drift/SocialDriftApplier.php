<?php

namespace App\Domains\Social\Drift;

use App\Domains\Social\SocialRelationState;
use App\Domains\Social\Enums\SocialImpactEvent;
use App\Domains\Social\Enums\RelationshipTone;

class SocialDriftApplier
{
    public function apply(SocialRelationState $state, SocialImpactEvent $event): SocialRelationState
    {
        // Clone state to ensure immutability if needed, but here we modify object
        // Assuming $state is a mutable DTO for this context
        
        switch ($event) {
            case SocialImpactEvent::SAVED_LIFE:
                $state->respect = min(100, $state->respect + 30);
                $state->familiarity = min(100, $state->familiarity + 10);
                $state->hostility = max(0, $state->hostility - 50);
                break;
                
            case SocialImpactEvent::BETRAYAL:
                $state->hostility = min(100, $state->hostility + 50);
                $state->respect = max(0, $state->respect - 30);
                // Familiarity might actually INCREASE (you know them better now), but let's keep it simple
                break;
                
            case SocialImpactEvent::TEACHING:
                $state->respect = min(100, $state->respect + 10);
                $state->familiarity = min(100, $state->familiarity + 5);
                break;

            case SocialImpactEvent::INSULT:
                $state->hostility = min(100, $state->hostility + 10);
                $state->respect = max(0, $state->respect - 5);
                break;

            case SocialImpactEvent::SHARED_TRAUMA:
                $state->familiarity = min(100, $state->familiarity + 20);
                break;
        }
        
        $this->recalculateTone($state);
        
        return $state;
    }

    private function recalculateTone(SocialRelationState $state): void
    {
        if ($state->hostility > 50) {
            $state->tone = RelationshipTone::HOSTILE;
        } elseif ($state->respect > 70) {
            $state->tone = RelationshipTone::RESPECT;
        } elseif ($state->familiarity > 70) {
            $state->tone = RelationshipTone::INTIMATE;
        } else {
            $state->tone = RelationshipTone::NEUTRAL;
        }
    }
}
