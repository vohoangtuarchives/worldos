<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdvancedMaterialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Source Materials (Pre-mutation states)
        $this->seedSourceMaterials();

        // 2. Target Materials (Post-mutation states)
        $this->seedTargetMaterials();
    }

    private function seedSourceMaterials(): void
    {
        $materials = [
            [
                'code' => 'DIVINE_KINGSHIP',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'description' => 'Political system where the monarch is viewed as a living god or divinely appointed. High sacred authority but vulnerable to desacralization.',
                'origin_sources' => ['theocracy', 'monarchy'],
                'pressure_outputs' => ['authority' => 0.8, 'sacred' => 0.9, 'innovation_friction' => 0.4]
            ],
            [
                'code' => 'RITUAL_SACRIFICE',
                'ontology' => 'behavioral',
                'function' => 'stabilizing',
                'description' => 'Institutionalized offering of value (or life) to appease higher powers. Stabilizes social anxiety but creates high violence cost.',
                'origin_sources' => ['animism', 'catastrophe_response'],
                'pressure_outputs' => ['anxiety' => -0.6, 'violence' => 0.7, 'cohesion' => 0.5]
            ],
            [
                'code' => 'PROPHETIC_MOVEMENT',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'description' => 'Emergence of charismatic leaders claiming new revelation. Destabilizes existing order to propose new moral frameworks.',
                'origin_sources' => ['crisis', 'revelation'],
                'pressure_outputs' => ['change' => 0.8, 'order' => -0.5, 'faith' => 0.7]
            ],
            [
                'code' => 'GIFT_ECONOMY',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'description' => 'Economic system based on reciprocal exchange rather than market money. Builds strong social bonds but scales poorly.',
                'origin_sources' => ['tribal_customs', 'potlatch'],
                'pressure_outputs' => ['cohesion' => 0.8, 'hierarchy' => 0.3, 'scalability' => -0.7]
            ],
            [
                'code' => 'SCAPEGOATING',
                'ontology' => 'behavioral',
                'function' => 'stabilizing',
                'description' => 'Collective psychological mechanism to project blame onto a specific group. Temporarily reduces internal conflict by focusing hostility outward.',
                'origin_sources' => ['plague', 'social_tension'],
                'pressure_outputs' => ['cohesion' => 0.4, 'violence' => 0.6, 'minority_oppression' => 0.9]
            ],
            [
                'code' => 'ORAL_TRADITION',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'description' => 'Transmission of culture and history through spoken word. Highly flexible but subject to drift and memory reshaping.',
                'origin_sources' => ['pre-literate_society', 'bards'],
                'pressure_outputs' => ['memory' => 0.6, 'myth_drift' => 0.8, 'rigidity' => -0.4]
            ],
            [
                'code' => 'FEUDAL_OATH',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'description' => 'Personal bonds of loyalty between lord and vassal. Decentralized power structure relying on honor.',
                'origin_sources' => ['state_collapse', 'warlordism'],
                'pressure_outputs' => ['decentralization' => 0.7, 'honor' => 0.8, 'state_weakness' => 0.5]
            ],
            [
                'code' => 'MARKET_CITY',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'description' => 'Urban center dominated by commerce and trade. Generates wealth and autonomy but undermines traditional hierarchy.',
                'origin_sources' => ['trade_routes', 'urbanization'],
                'pressure_outputs' => ['trade' => 0.9, 'autonomy' => 0.7, 'wealth_concentration' => 0.6]
            ]
        ];

        foreach ($materials as $data) {
            $this->createMaterial($data);
        }
    }

    private function seedTargetMaterials(): void
    {
        $materials = [
            // From DIVINE_KINGSHIP
            [
                'code' => 'THEOCRATIC_STATE',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'description' => 'State apparatus fully merged with religious hierarchy. Law is divine will. Extremely stable but intolerant of change.',
                'origin_sources' => ['divine_kingship_evolution'],
                'pressure_outputs' => ['order' => 0.9, 'innovation' => -0.8, 'dissent' => -0.9]
            ],
            [
                'code' => 'SYMBOLIC_MONARCHY',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'description' => 'Monarch serves as a figurehead of national unity with no political power. Purely symbolic authority.',
                'origin_sources' => ['divine_kingship_decay'],
                'pressure_outputs' => ['unity' => 0.6, 'power' => 0.1, 'stability' => 0.5]
            ],

            // From RITUAL_SACRIFICE
            [
                'code' => 'SYMBOLIC_OFFERING',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'description' => 'Evolution of sacrifice into harmless symbolic acts (burning paper, pouring wine). Retains function without violence.',
                'origin_sources' => ['ritual_reform'],
                'pressure_outputs' => ['anxiety' => -0.4, 'violence' => 0.1, 'culture' => 0.5]
            ],
            [
                'code' => 'MASS_PURGE',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'description' => 'Ritual sacrifice escalated to state-level elimination of "undesirables". High terror and control.',
                'origin_sources' => ['totalitarianism'],
                'pressure_outputs' => ['terror' => 0.9, 'population' => -0.5, 'dissent' => -1.0]
            ],

            // From PROPHETIC_MOVEMENT
            [
                'code' => 'MESSIANIC_CULT',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'description' => 'High-intensity devoted group focused on an apocalyptic savior. Volatile and isolated.',
                'origin_sources' => ['radicalization'],
                'pressure_outputs' => ['fanaticism' => 0.9, 'cohesion' => 0.8, 'rationality' => -0.7]
            ],
            [
                'code' => 'REFORMATION',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'description' => 'Institutional restructuring driven by critique of corruption. Modernizes old systems.',
                'origin_sources' => ['religious_critique'],
                'pressure_outputs' => ['efficiency' => 0.7, 'corruption' => -0.6, 'schism' => 0.5]
            ],

            // From GIFT_ECONOMY
            [
                'code' => 'PATRONAGE_NETWORK',
                'ontology' => 'institutional',
                'function' => 'stabilizing',
                'description' => 'Formalized system of favors and clientelism. unequal but stable vertical relationships.',
                'origin_sources' => ['political_machine'],
                'pressure_outputs' => ['corruption' => 0.5, 'stability' => 0.7, 'meritocracy' => -0.6]
            ],
            [
                'code' => 'BRIBE_CULTURE',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'description' => 'Systemic corruption where everything has a price. Erodes trust and efficiency.',
                'origin_sources' => ['institutional_decay'],
                'pressure_outputs' => ['trust' => -0.9, 'efficiency' => -0.8, 'inequality' => 0.7]
            ],

            // From SCAPEGOATING
            [
                'code' => 'INSTITUTIONALIZED_RACISM',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'description' => 'Codified legal or structural oppression of specific groups. Creates permanent underclass.',
                'origin_sources' => ['colonialism', 'legislation'],
                'pressure_outputs' => ['inequality' => 0.9, 'potential_conflict' => 0.8, 'human_capital' => -0.5]
            ],
            [
                'code' => 'WITCH_HUNT',
                'ontology' => 'behavioral',
                'function' => 'destructive',
                'description' => 'Paranoid purification campaigns seeking hidden enemies within. Destroys social trust.',
                'origin_sources' => ['moral_panic'],
                'pressure_outputs' => ['paranoia' => 0.9, 'trust' => -1.0, 'innovation' => -0.6]
            ],

            // From ORAL_TRADITION
            [
                'code' => 'MYTHOLOGIZATION',
                'ontology' => 'symbolic',
                'function' => 'transformative',
                'description' => 'Transformation of history into pure myth. Facts are lost, meaning is amplified.',
                'origin_sources' => ['time_passage'],
                'pressure_outputs' => ['meaning' => 0.8, 'truth' => -0.7, 'national_identity' => 0.9]
            ],
            [
                'code' => 'SECRET_SOCIETY',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'description' => 'Knowledge restricted to initiates. Creates hidden power structures.',
                'origin_sources' => ['esotericism'],
                'pressure_outputs' => ['conspiracy' => 0.7, 'elitism' => 0.8, 'transparency' => -0.9]
            ],

            // From FEUDAL_OATH
            [
                'code' => 'CHIVALRIC_CODE',
                'ontology' => 'symbolic',
                'function' => 'stabilizing',
                'description' => 'Elaborate code of conduct wrapping violence in honor and ritual. Civilizes warfare.',
                'origin_sources' => ['knight_culture'],
                'pressure_outputs' => ['civilization' => 0.6, 'violence_constraint' => 0.5, 'romance' => 0.7]
            ],
            [
                'code' => 'MAFIA_CODE',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'description' => 'Code of silence (Omerta) and loyalty used for criminal enterprise.',
                'origin_sources' => ['underground'],
                'pressure_outputs' => ['crime' => 0.9, 'state_authority' => -0.6, 'community_fear' => 0.8]
            ],

            // From MARKET_CITY
            [
                'code' => 'MERCHANT_REPUBLIC',
                'ontology' => 'institutional',
                'function' => 'transformative',
                'description' => 'State ruled by merchant councils. Prioritizes profit and trade over land or blood.',
                'origin_sources' => ['venice_model'],
                'pressure_outputs' => ['wealth' => 0.9, 'military_mercenary' => 0.7, 'aristocracy' => -0.4]
            ],
            [
                'code' => 'PLUTOCRACY',
                'ontology' => 'institutional',
                'function' => 'destructive',
                'description' => 'Rule by density of wealth. Law serves the rich specifically.',
                'origin_sources' => ['late_capitalism'],
                'pressure_outputs' => ['inequality' => 1.0, 'social_mobility' => -0.8, 'resentment' => 0.9]
            ]
        ];

        foreach ($materials as $data) {
            $this->createMaterial($data);
        }
    }

    private function createMaterial(array $data): void
    {
        if (DB::table('materials')->where('code', $data['code'])->exists()) {
            return;
        }

        DB::table('materials')->insert([
            'id' => Str::uuid()->toString(),
            'code' => $data['code'],
            'ontology' => $data['ontology'],
            'function' => $data['function'],
            'default_lifecycle' => 'active',
            'description' => $data['description'],
            'origin_sources' => json_encode($data['origin_sources']),
            'pressure_outputs' => json_encode($data['pressure_outputs']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
