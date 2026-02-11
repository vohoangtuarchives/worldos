<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\GenreValidator;
use App\Domains\Genre\DTO\StoryEvent;
use App\Domains\Genre\Validation\ValidationResult;
use App\Domains\Genre\Validation\Violations\ForbiddenConcept;
use App\Domains\Genre\Validation\Violations\ImpossibleEvent;

class SurvivalGenreValidator implements GenreValidator
{
    public function validateEvent(StoryEvent $event): ValidationResult
    {
        $violations = [];

        // Rule 1: No Resurrection
        if ($event->type === 'resurrection' || ($event->payload['description'] ?? '') === 'revival') {
             $violations[] = new ImpossibleEvent(
                "Resurrection is impossible in Survival genre. Death is final.", 
                $event
            );
        }

        // Rule 2: No Magic / System Cheats
        if (in_array($event->type, ['spell_cast', 'system_reward', 'level_up'])) {
            $violations[] = new ForbiddenConcept(
                "Magic/System events are forbidden. Survival relies on physical reality.", 
                $event
            );
        }

        // Rule 3: No "Easy" Food
        if (($event->payload['source'] ?? '') === 'create_food_spell') {
             $violations[] = new ImpossibleEvent(
                "Cannot conjure food. Must be scavenged.",
                $event
            );
        }

        if (!empty($violations)) {
            return ValidationResult::fail($violations, false); // Not repairable usually, request rewrite
        }

        return ValidationResult::pass();
    }
}
