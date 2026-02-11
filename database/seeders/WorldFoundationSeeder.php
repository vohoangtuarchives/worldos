<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorldPrimitive;
use App\Models\WFRVersion;

class WorldFoundationSeeder extends Seeder
{
    public function run(): void
    {
        // Create WFR v1.0.0
        WFRVersion::create([
            'version' => '1.0.0',
            'changelog' => 'Initial World Foundation Repository release with 25 core primitives across 5 domains.',
            'released_at' => now(),
            'is_stable' => true,
        ]);

        // Seed primitives (25 total - 5 per domain)
        $primitives = [
            // CIVILIZATION (5)
            ['domain' => 'civilization', 'code' => 'MONARCHY', 'name' => 'Monarchy', 'description' => 'Hereditary rule by single sovereign', 'constraints' => ['requires' => ['bloodline'], 'enables' => ['court_intrigue']]],
            ['domain' => 'civilization', 'code' => 'REPUBLIC', 'name' => 'Republic', 'description' => 'Representative governance by elected officials', 'constraints' => ['requires' => ['citizenship'], 'enables' => ['factional_politics']]],
            ['domain' => 'civilization', 'code' => 'THEOCRACY', 'name' => 'Theocracy', 'description' => 'Rule by religious authority', 'constraints' => ['requires' => ['divine_mandate'], 'enables' => ['religious_law']]],
            ['domain' => 'civilization', 'code' => 'TRIBAL_FEDERATION', 'name' => 'Tribal Federation', 'description' => 'Confederation of autonomous tribes', 'constraints' => ['enables' => ['tribal_warfare', 'kinship_bonds']]],
            ['domain' => 'civilization', 'code' => 'EMPIRE', 'name' => 'Empire', 'description' => 'Expansionist centralized authority over multiple territories', 'constraints' => ['requires' => ['military_power'], 'enables' => ['conquest', 'tribute']]],

            // CULTURE (5)
            ['domain' => 'culture', 'code' => 'HONOR_BASED', 'name' => 'Honor-Based Culture', 'description' => 'Society values personal honor above all', 'constraints' => ['enables' => ['dueling', 'blood_feuds'], 'forbids' => ['dishonor_tolerance']]],
            ['domain' => 'culture', 'code' => 'WEALTH_BASED', 'name' => 'Wealth-Based Culture', 'description' => 'Social status determined by material wealth', 'constraints' => ['enables' => ['mercantilism', 'plutocracy']]],
            ['domain' => 'culture', 'code' => 'FAITH_BASED', 'name' => 'Faith-Based Culture', 'description' => 'Religious devotion as highest virtue', 'constraints' => ['enables' => ['holy_wars', 'martyrdom']]],
            ['domain' => 'culture', 'code' => 'KNOWLEDGE_BASED', 'name' => 'Knowledge-Based Culture', 'description' => 'Scholarship and wisdom as primary values', 'constraints' => ['enables' => ['academies', 'philosophical_schools']]],
            ['domain' => 'culture', 'code' => 'SURVIVAL_BASED', 'name' => 'Survival-Based Culture', 'description' => 'Pragmatic focus on endurance and adaptation', 'constraints' => ['enables' => ['resource_hoarding', 'harsh_justice']]],

            // ECONOMY (5)
            ['domain' => 'economy', 'code' => 'BARTER_ECONOMY', 'name' => 'Barter Economy', 'description' => 'Direct exchange of goods and services', 'constraints' => ['forbids' => ['currency'], 'enables' => ['trade_caravans']]],
            ['domain' => 'economy', 'code' => 'COIN_ECONOMY', 'name' => 'Coin Economy', 'description' => 'Standardized currency-based trade', 'constraints' => ['enables' => ['banking', 'taxation']]],
            ['domain' => 'economy', 'code' => 'TRIBUTE_ECONOMY', 'name' => 'Tribute Economy', 'description' => 'Wealth flows from vassals to overlords', 'constraints' => ['requires' => ['hierarchy'], 'enables' => ['patronage']]],
            ['domain' => 'economy', 'code' => 'MANA_ECONOMY', 'name' => 'Mana Economy', 'description' => 'Magical energy as primary resource', 'constraints' => ['requires' => ['magic_system'], 'enables' => ['enchanting', 'spell_trade']]],
            ['domain' => 'economy', 'code' => 'GIFT_ECONOMY', 'name' => 'Gift Economy', 'description' => 'Social bonds through reciprocal giving', 'constraints' => ['enables' => ['obligation_networks'], 'forbids' => ['profit_motive']]],

            // POWER (5)
            ['domain' => 'power', 'code' => 'MILITARY_POWER', 'name' => 'Military Power', 'description' => 'Authority through force of arms', 'constraints' => ['enables' => ['conquest', 'warlords']]],
            ['domain' => 'power', 'code' => 'DIVINE_RIGHT', 'name' => 'Divine Right', 'description' => 'Legitimacy from gods or heaven', 'constraints' => ['requires' => ['religious_authority'], 'enables' => ['inquisition']]],
            ['domain' => 'power', 'code' => 'BLOODLINE_LEGITIMACY', 'name' => 'Bloodline Legitimacy', 'description' => 'Authority inherited through ancestry', 'constraints' => ['enables' => ['succession_wars'], 'forbids' => ['meritocracy']]],
            ['domain' => 'power', 'code' => 'POPULAR_SUPPORT', 'name' => 'Popular Support', 'description' => 'Power derived from public will', 'constraints' => ['enables' => ['democracy', 'populism']]],
            ['domain' => 'power', 'code' => 'KNOWLEDGE_POWER', 'name' => 'Knowledge Power', 'description' => 'Control through monopoly of knowledge', 'constraints' => ['enables' => ['secret_societies', 'technocracy']]],

            // ONTOLOGICAL (5)
            ['domain' => 'ontological', 'code' => 'MORTAL_ONLY', 'name' => 'Mortal-Only Cosmos', 'description' => 'Only mortal beings exist', 'constraints' => ['forbids' => ['spirits', 'gods', 'undead']]],
            ['domain' => 'ontological', 'code' => 'SPIRIT_COEXIST', 'name' => 'Spirit Coexistence', 'description' => 'Spirits and mortals share reality', 'constraints' => ['enables' => ['shamanism', 'possession']]],
            ['domain' => 'ontological', 'code' => 'DEATH_PERMANENT', 'name' => 'Permanent Death', 'description' => 'Death is irreversible', 'constraints' => ['forbids' => ['resurrection', 'reincarnation']]],
            ['domain' => 'ontological', 'code' => 'DEATH_REVERSIBLE', 'name' => 'Reversible Death', 'description' => 'Death can be undone through magic or ritual', 'constraints' => ['enables' => ['necromancy', 'resurrection_magic']]],
            ['domain' => 'ontological', 'code' => 'MAGIC_WILD', 'name' => 'Wild Magic', 'description' => 'Magic is chaotic and unpredictable', 'constraints' => ['forbids' => ['systematic_magic'], 'enables' => ['chaos_sorcery']]],
        ];

        foreach ($primitives as $primitive) {
            WorldPrimitive::insert([
                'domain' => $primitive['domain'],
                'code' => $primitive['code'],
                'name' => $primitive['name'],
                'description' => $primitive['description'],
                'constraints' => json_encode($primitive['constraints']),
                'version' => '1.0.0',
                'tags' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Seeded 25 primitives (WFR v1.0.0)');
    }
}
