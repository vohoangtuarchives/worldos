<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Tuzy\Domain\CognitiveKernel\Archetype;

class ArchetypeSeeder extends Seeder
{
    /**
     * Core archetypes based on ARCHETYPE_DRIFT.md governance
     * 20-30 archetypes across 4 domains
     */
    public function run(): void
    {
        $archetypes = [
            // PERCEPTION DOMAIN (how reality is understood)
            [
                'key' => 'silence',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['order', 'chaos'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'The value placed on silence, quietness, and restraint in communication'
            ],
            [
                'key' => 'noise',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['chaos', 'vitality'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'The value of loudness, expression, and constant communication'
            ],
            [
                'key' => 'memory',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['preservation', 'burden'],
                'baseline_weight' => 0.6,
                'volatility' => 0.2,
                'description' => 'The importance of remembering history and maintaining records'
            ],
            [
                'key' => 'forgetting',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['freedom', 'ignorance'],
                'baseline_weight' => 0.4,
                'volatility' => 0.3,
                'description' => 'The virtue of letting go of the past'
            ],
            [
                'key' => 'fear',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['caution', 'paralysis'],
                'baseline_weight' => 0.5,
                'volatility' => 0.4,
                'description' => 'Fear as a guiding principle for decision-making'
            ],
            [
                'key' => 'truth',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['clarity', 'brutality'],
                'baseline_weight' => 0.6,
                'volatility' => 0.3,
                'description' => 'The pursuit and value of objective truth'
            ],
            [
                'key' => 'illusion',
                'domain' => Archetype::DOMAIN_PERCEPTION,
                'polarity' => ['comfort', 'deception'],
                'baseline_weight' => 0.4,
                'volatility' => 0.3,
                'description' => 'The acceptance of comforting falsehoods'
            ],

            // POWER DOMAIN (authority and control)
            [
                'key' => 'sacrifice',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['redemptive', 'extractive'],
                'baseline_weight' => 0.5,
                'volatility' => 0.4,
                'description' => 'The necessity and virtue of sacrifice for the greater good'
            ],
            [
                'key' => 'domination',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['order', 'oppression'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Power through direct control and subjugation'
            ],
            [
                'key' => 'balance',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['harmony', 'stagnation'],
                'baseline_weight' => 0.6,
                'volatility' => 0.2,
                'description' => 'Power distributed in equilibrium'
            ],
            [
                'key' => 'transcendence',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['ascension', 'escape'],
                'baseline_weight' => 0.4,
                'volatility' => 0.3,
                'description' => 'Power through rising above mortal concerns'
            ],
            [
                'key' => 'rebellion',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['freedom', 'chaos'],
                'baseline_weight' => 0.4,
                'volatility' => 0.4,
                'description' => 'Resistance to authority as virtue'
            ],
            [
                'key' => 'submission',
                'domain' => Archetype::DOMAIN_POWER,
                'polarity' => ['order', 'weakness'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Obedience to authority as virtue'
            ],

            // SOCIAL DOMAIN (relationships and society)
            [
                'key' => 'unity',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['strength', 'conformity'],
                'baseline_weight' => 0.6,
                'volatility' => 0.3,
                'description' => 'Collective identity and togetherness'
            ],
            [
                'key' => 'diversity',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['richness', 'division'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Value placed on difference and variety'
            ],
            [
                'key' => 'hierarchy',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['order', 'inequality'],
                'baseline_weight' => 0.6,
                'volatility' => 0.2,
                'description' => 'Stratified social structure'
            ],
            [
                'key' => 'equality',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['justice', 'uniformity'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'All members of society treated the same'
            ],
            [
                'key' => 'purity',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['perfection', 'exclusion'],
                'baseline_weight' => 0.4,
                'volatility' => 0.4,
                'description' => 'Maintenance of social or cultural purity'
            ],
            [
                'key' => 'contamination',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['mixing', 'corruption'],
                'baseline_weight' => 0.4,
                'volatility' => 0.4,
                'description' => 'Acceptance or fear of impurity'
            ],
            [
                'key' => 'kinship',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['loyalty', 'nepotism'],
                'baseline_weight' => 0.6,
                'volatility' => 0.2,
                'description' => 'Family and blood ties as primary social bond'
            ],
            [
                'key' => 'merit',
                'domain' => Archetype::DOMAIN_SOCIAL,
                'polarity' => ['achievement', 'ruthlessness'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Individual accomplishment as basis for status'
            ],

            // METAPHYSICAL DOMAIN (existence and meaning)
            [
                'key' => 'decay',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['realism', 'nihilism'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Inevitable decline and entropy'
            ],
            [
                'key' => 'renewal',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['hope', 'denial'],
                'baseline_weight' => 0.5,
                'volatility' => 0.3,
                'description' => 'Cyclical rebirth and regeneration'
            ],
            [
                'key' => 'eternity',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['permanence', 'stagnation'],
                'baseline_weight' => 0.4,
                'volatility' => 0.2,
                'description' => 'Timelessness and immortality as ideal'
            ],
            [
                'key' => 'mortality',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['urgency', 'despair'],
                'baseline_weight' => 0.6,
                'volatility' => 0.3,
                'description' => 'Finite existence and death'
            ],
            [
                'key' => 'recursion',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['pattern', 'trap'],
                'baseline_weight' => 0.4,
                'volatility' => 0.3,
                'description' => 'Repeating cycles and self-reference'
            ],
            [
                'key' => 'oblivion',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['release', 'extinction'],
                'baseline_weight' => 0.3,
                'volatility' => 0.4,
                'description' => 'Complete cessation and nothingness'
            ],
            [
                'key' => 'purpose',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['meaning', 'delusion'],
                'baseline_weight' => 0.6,
                'volatility' => 0.3,
                'description' => 'Belief in inherent meaning and destiny'
            ],
            [
                'key' => 'absurdity',
                'domain' => Archetype::DOMAIN_METAPHYSICAL,
                'polarity' => ['freedom', 'meaninglessness'],
                'baseline_weight' => 0.4,
                'volatility' => 0.4,
                'description' => 'Lack of inherent meaning in existence'
            ],
        ];

        foreach ($archetypes as $data) {
            Archetype::create(array_merge($data, [
                'version' => '1.0.0'
            ]));
        }
    }
}
