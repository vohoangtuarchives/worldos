<?php

namespace App\Domains\Reader;

use App\Domains\Material\State\WorldState;

/**
 * ChoicePresenter - Generate meaningful choices based on WorldState
 * 
 * Presents choices to readers that affect simulation trajectory.
 */
class ChoicePresenter
{
    /**
     * Generate choices based on current WorldState.
     * 
     * @param WorldState $state
     * @param int $epoch
     * @return array Available choices
     */
    public function generateChoices(WorldState $state, int $epoch): array
    {
        $choices = [];

        // Structural choices
        $choices = array_merge($choices, $this->generateStructuralChoices($state, $epoch));

        // Symbolic choices
        $choices = array_merge($choices, $this->generateSymbolicChoices($state, $epoch));

        // Memory choices
        $choices = array_merge($choices, $this->generateMemoryChoices($state, $epoch));

        // Interaction choices
        $choices = array_merge($choices, $this->generateInteractionChoices($state, $epoch));

        return $choices;
    }

    /**
     * Generate structural choices (power/economy).
     */
    private function generateStructuralChoices(WorldState $state, int $epoch): array
    {
        $choices = [];

        // High inequality
        if ($state->structural->inequality > 0.7) {
            $choices[] = [
                'id' => "redistribute_wealth_{$epoch}",
                'category' => 'structural',
                'question' => 'The gap between rich and poor widens. What should be done?',
                'options' => [
                    [
                        'id' => 'redistribute',
                        'text' => 'Redistribute wealth to the masses',
                        'delta' => ['inequality' => -0.2, 'centralization' => 0.1],
                    ],
                    [
                        'id' => 'maintain',
                        'text' => 'Maintain the current system',
                        'delta' => ['inequality' => 0.0],
                    ],
                    [
                        'id' => 'strengthen',
                        'text' => 'Strengthen property rights',
                        'delta' => ['inequality' => 0.1, 'productivity' => 0.05],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        // Low infrastructure
        if ($state->structural->infrastructureIntegrity < 0.4) {
            $choices[] = [
                'id' => "build_infrastructure_{$epoch}",
                'category' => 'structural',
                'question' => 'Infrastructure crumbles. Should resources be invested?',
                'options' => [
                    [
                        'id' => 'invest',
                        'text' => 'Invest heavily in infrastructure',
                        'delta' => ['infrastructure_integrity' => 0.3, 'resources' => -0.1],
                    ],
                    [
                        'id' => 'minimal',
                        'text' => 'Minimal maintenance only',
                        'delta' => ['infrastructure_integrity' => 0.05],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        return $choices;
    }

    /**
     * Generate symbolic choices (belief/identity).
     */
    private function generateSymbolicChoices(WorldState $state, int $epoch): array
    {
        $choices = [];

        // Low myth strength
        if ($state->symbolic->mythStrength < 0.3) {
            $choices[] = [
                'id' => "revive_traditions_{$epoch}",
                'category' => 'symbolic',
                'question' => 'Ancient beliefs fade. Should they be preserved?',
                'options' => [
                    [
                        'id' => 'revive',
                        'text' => 'Revive and strengthen traditions',
                        'delta' => ['myth_strength' => 0.3, 'ritualization' => 0.2],
                    ],
                    [
                        'id' => 'let_fade',
                        'text' => 'Let them fade naturally',
                        'delta' => ['myth_strength' => -0.1, 'identity_rigidity' => -0.1],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        // High belief extremism
        if ($state->symbolic->beliefExtremism > 0.7) {
            $choices[] = [
                'id' => "moderate_beliefs_{$epoch}",
                'category' => 'symbolic',
                'question' => 'Extremism grips the faithful. Should moderation be encouraged?',
                'options' => [
                    [
                        'id' => 'moderate',
                        'text' => 'Encourage moderation and tolerance',
                        'delta' => ['belief_extremism' => -0.2, 'legitimacy' => 0.1],
                    ],
                    [
                        'id' => 'allow',
                        'text' => 'Allow beliefs to intensify',
                        'delta' => ['belief_extremism' => 0.1, 'identity_rigidity' => 0.15],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        return $choices;
    }

    /**
     * Generate memory choices (historical consciousness).
     */
    private function generateMemoryChoices(WorldState $state, int $epoch): array
    {
        $choices = [];

        // High trauma
        if ($state->memory->traumaDensity > 0.6) {
            $choices[] = [
                'id' => "address_trauma_{$epoch}",
                'category' => 'memory',
                'question' => 'Collective trauma weighs heavily. How should it be addressed?',
                'options' => [
                    [
                        'id' => 'heal',
                        'text' => 'Create spaces for healing and remembrance',
                        'delta' => ['trauma_density' => -0.15, 'nostalgia' => 0.1],
                    ],
                    [
                        'id' => 'suppress',
                        'text' => 'Suppress painful memories',
                        'delta' => ['trauma_density' => 0.05, 'historical_distortion' => 0.2],
                    ],
                    [
                        'id' => 'move_on',
                        'text' => 'Focus on the future',
                        'delta' => ['trauma_density' => -0.05, 'legacy_load' => -0.1],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        return $choices;
    }

    /**
     * Generate interaction choices (external relations).
     */
    private function generateInteractionChoices(WorldState $state, int $epoch): array
    {
        $choices = [];

        // High external threat
        if ($state->interaction->externalThreat > 0.6) {
            $choices[] = [
                'id' => "respond_to_threat_{$epoch}",
                'category' => 'interaction',
                'question' => 'External forces threaten the realm. How should it respond?',
                'options' => [
                    [
                        'id' => 'fortify',
                        'text' => 'Fortify defenses and close borders',
                        'delta' => ['external_threat' => -0.1, 'migration_pressure' => -0.2],
                    ],
                    [
                        'id' => 'negotiate',
                        'text' => 'Seek diplomatic solutions',
                        'delta' => ['external_threat' => -0.15, 'trade_integration' => 0.1],
                    ],
                    [
                        'id' => 'ignore',
                        'text' => 'Ignore the threat',
                        'delta' => ['external_threat' => 0.1],
                    ],
                ],
                'epoch' => $epoch,
            ];
        }

        return $choices;
    }
}
