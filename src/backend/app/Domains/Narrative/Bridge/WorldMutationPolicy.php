<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge;

use App\Domains\Narrative\Bridge\DTO\StoryEvent;

/**
 * Maps narrative story events to deltas on narrative_driven_state.
 * Apply deltas to current state (clamped 0–1); state is then injected into next chapter prompt.
 */
final class WorldMutationPolicy
{
    private const DIMENSIONS = ['shadow_presence', 'magic_stability', 'threat_level'];

    /**
     * Default state when none exists yet.
     */
    public static function defaultState(): array
    {
        return [
            'shadow_presence' => 0.0,
            'magic_stability' => 1.0,
            'threat_level' => 0.0,
        ];
    }

    /**
     * Compute deltas from events and merge into current state.
     *
     * @param list<StoryEvent> $events
     * @param array<string, float> $currentState
     * @return array<string, float>
     */
    public function applyEvents(array $events, array $currentState): array
    {
        $state = array_merge(self::defaultState(), $currentState);
        $state = array_intersect_key($state, array_flip(self::DIMENSIONS));

        foreach ($events as $event) {
            $deltas = $this->deltasForEvent($event);
            foreach ($deltas as $key => $delta) {
                if (isset($state[$key])) {
                    $state[$key] = $this->clamp($state[$key] + $delta);
                }
            }
        }

        return $state;
    }

    /**
     * @return array<string, float>
     */
    private function deltasForEvent(StoryEvent $event): array
    {
        return match ($event->type) {
            'magic_corruption' => [
                'shadow_presence' => $event->severity * 0.4,
                'magic_stability' => -$event->severity * 0.3,
                'threat_level' => $event->severity * 0.2,
            ],
            'magic_collapse' => [
                'shadow_presence' => $event->severity * 0.3,
                'magic_stability' => -$event->severity * 0.5,
                'threat_level' => $event->severity * 0.3,
            ],
            'invasion', 'violence' => [
                'shadow_presence' => $event->severity * 0.2,
                'threat_level' => $event->severity * 0.4,
            ],
            'threat_rise' => [
                'shadow_presence' => $event->severity * 0.35,
                'threat_level' => $event->severity * 0.45,
            ],
            'relationship_fracture' => [
                'threat_level' => $event->severity * 0.2,
            ],
            default => [],
        };
    }

    private function clamp(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }
}
