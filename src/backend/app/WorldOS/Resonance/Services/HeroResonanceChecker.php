<?php

declare(strict_types=1);

namespace App\WorldOS\Resonance\Services;

use App\WorldOS\Resonance\Contracts\ResonanceCheckerInterface;
use App\WorldOS\Resonance\ValueObjects\HeroArchetype;
use App\WorldOS\Resonance\ValueObjects\ResonanceEvent;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Hero Resonance Checker — threshold-based hero spawning.
 *
 * From WORLDOS_BACKEND_DOCUMENTATION.md §5.2:
 *   Entropy > 0.8 → REBEL_LEADER
 *   Entropy > 0.9 → SAVIOR
 *   Order > 0.9  → REFORMER
 *   Order > 0.95 → PHILOSOPHER_KING
 *   Cohesion < 0.3 → CULTURAL_HERO
 *
 * Pure computation — NO Laravel dependencies, NO side effects.
 */
final class HeroResonanceChecker implements ResonanceCheckerInterface
{
    /**
     * @return ResonanceEvent[]
     */
    public function check(
        WorldStateVector $state,
        CascadeStateVector $cascade,
    ): array {
        $events = [];

        // SAVIOR — existential threat (supersedes REBEL)
        if ($state->entropy > 0.9) {
            $events[] = ResonanceEvent::heroSpawn(
                archetype: HeroArchetype::SAVIOR,
                magnitude: $state->entropy,
                description: "Entropy at {$state->entropy} — existential threat spawns a Savior",
                conditions: [
                    'entropy' => $state->entropy,
                    'trauma' => $state->trauma,
                ],
            );
        }
        // REBEL_LEADER — high entropy but not yet existential
        elseif ($state->entropy > 0.8) {
            $events[] = ResonanceEvent::heroSpawn(
                archetype: HeroArchetype::REBEL_LEADER,
                magnitude: $state->entropy,
                description: "Entropy at {$state->entropy} — rising chaos spawns a Rebel Leader",
                conditions: [
                    'entropy' => $state->entropy,
                    'inequality' => $state->inequality,
                ],
            );
        }

        // PHILOSOPHER_KING — extreme order (supersedes REFORMER)
        if ($state->order > 0.95) {
            $events[] = ResonanceEvent::heroSpawn(
                archetype: HeroArchetype::PHILOSOPHER_KING,
                magnitude: $state->order,
                description: "Order at {$state->order} — enlightened absolutist emerges",
                conditions: [
                    'order' => $state->order,
                    'cohesion' => $state->cohesion,
                ],
            );
        }
        // REFORMER — high order but not extreme
        elseif ($state->order > 0.9) {
            $events[] = ResonanceEvent::heroSpawn(
                archetype: HeroArchetype::REFORMER,
                magnitude: $state->order,
                description: "Order at {$state->order} — reformer rises against rigid structure",
                conditions: [
                    'order' => $state->order,
                    'inequality' => $state->inequality,
                ],
            );
        }

        // CULTURAL_HERO — fragmented society
        if ($state->cohesion < 0.3) {
            $events[] = ResonanceEvent::heroSpawn(
                archetype: HeroArchetype::CULTURAL_HERO,
                magnitude: 1.0 - $state->cohesion,
                description: "Cohesion at {$state->cohesion} — cultural hero unifies fragments",
                conditions: [
                    'cohesion' => $state->cohesion,
                    'trauma' => $state->trauma,
                ],
            );
        }

        return $events;
    }
}
