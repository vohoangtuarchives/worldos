<?php

namespace App\Domains\Genre\Validation;

use App\Domains\Genre\Contracts\GenreValidator;
use App\Domains\Genre\Contracts\GenreDefinition;
use Tuzy\Domain\Genre\ValueObject\StoryEvent;
use Tuzy\Domain\Genre\ValueObject\ValidationResult;
use Tuzy\Domain\Genre\ValueObject\ImpossibleEvent;
use Tuzy\Domain\Genre\ValueObject\PowerLevelViolation;
use Tuzy\Domain\Genre\ValueObject\ForbiddenConcept;

class XianxiaGenreValidator implements GenreValidator
{
    public function validateEvent(
        GenreDefinition $genre,
        object $worldState, // Placeholder for WorldState
        StoryEvent $event
    ): ValidationResult {
        return match ($event->type) {
            'combat' => $this->validateCombat($worldState, $event),
            'death' => $this->validateDeath($worldState, $event),
            'resurrection' => $this->validateResurrection($worldState, $event),
            default => new ValidationResult(true),
        };
    }

    private function validateCombat(object $world, StoryEvent $event): ValidationResult 
    {
        // Mock WorldState logic for now until WorldState is fully typed
        // Assuming $world has ->character($id) ->realm ->tier
        
        // Pseudo-implementation:
        /*
        $a = $world->character($event->payload['attacker']);
        $d = $world->character($event->payload['defender']);

        $violations = [];

        // 1. Mortal vs Immortal
        if ($this->isMortal($a) && $this->isImmortal($d)) {
             $violations[] = new ImpossibleEvent('Mortal cannot harm Immortal');
        }
        
        return new ValidationResult(empty($violations), $violations, true);
        */
        
        return new ValidationResult(true); 
    }

    private function validateDeath(object $world, StoryEvent $event): ValidationResult
    {
        return new ValidationResult(true);
    }

    private function validateResurrection(object $world, StoryEvent $event): ValidationResult
    {
        $method = $event->payload['method'] ?? null;
        $violations = [];

        if (!in_array($method, ['reincarnation', 'heavenly_intervention', 'dao_reversal'])) {
             $violations[] = new ForbiddenConcept('Invalid resurrection method (HP respawn not allowed)');
        }

        return new ValidationResult(empty($violations), $violations, true);
    }
    
    private function isMortal($char): bool { return false; } // Stub
    private function isImmortal($char): bool { return true; } // Stub
}
