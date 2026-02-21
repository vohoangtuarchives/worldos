<?php

namespace Tuzy\Application\Genre\Genres\Survival;

use Tuzy\Domain\Genre\Contracts\GenreValidator;
use Tuzy\Domain\Genre\ValueObject\StoryEvent;
use Tuzy\Domain\Genre\ValueObject\ValidationResult;
use Tuzy\Domain\Genre\ValueObject\ForbiddenConcept;
use Tuzy\Domain\Genre\ValueObject\ImpossibleEvent;

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
