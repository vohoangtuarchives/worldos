<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Vietnamese\Models\RealmContact;

class RealmContactSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chinese Domination (Bắc thuộc)
        // Era 3 (~111 BC) to Era 19 (~938 AD)
        // Roughly Era 2 to 18 in our 50-year chunks
        
        // 1st Domination (Triệu - Han)
        RealmContact::create([
            'realm_name' => 'Northern Empire (Han)',
            'period_name' => 'Bac Thuoc 1',
            'start_era' => 2,
            'end_era' => 8,
            'influence_type' => 'DOMINATION',
            'intensity' => 0.8,
            'description' => 'First period of Northern domination. Introduction of administrative system and Confucianism.',
            'metadata' => ['cultural_impact' => 'high', 'tech_transfer' => 'iron_plough']
        ]);

        // 2nd Domination (Tang)
        RealmContact::create([
            'realm_name' => 'Northern Empire (Tang)',
            'period_name' => 'An Nam Protectorate',
            'start_era' => 12,
            'end_era' => 18,
            'influence_type' => 'DOMINATION',
            'intensity' => 0.9,
            'description' => 'Peak of administrative integration. Rise of local elites.',
            'metadata' => ['buddhism_zen' => 'established']
        ]);

        // 2. Champa Encounters (South)
        // Ongoing from Era 4 to Era 30
        RealmContact::create([
            'realm_name' => 'Champa Kingdom',
            'period_name' => 'Champa Wars & Trade',
            'start_era' => 4,
            'end_era' => 30,
            'influence_type' => 'WAR', // Mixed War/Trade
            'intensity' => 0.6,
            'description' => 'Continuous interaction with Champa kingdom in the south.',
            'metadata' => ['cultural_exchange' => 'music_architecture']
        ]);

        // 3. French Colonial (Modern)
        // Era 92 (1858) to Era 98 (1954)
        RealmContact::create([
            'realm_name' => 'Western Empire (France)',
            'period_name' => 'French Indochina',
            'start_era' => 92,
            'end_era' => 98,
            'influence_type' => 'DOMINATION',
            'intensity' => 0.95,
            'description' => 'Colonial period. Introduction of western science, romanized script, and modern capitalism.',
            'metadata' => ['script_change' => 'quoc_ngu', 'modernization' => 'rapid']
        ]);
        
        // 4. US Intervention
        RealmContact::create([
            'realm_name' => 'Superpower (USA)',
            'period_name' => 'Vietnam War',
            'start_era' => 98,
            'end_era' => 99,
            'influence_type' => 'WAR',
            'intensity' => 1.0,
            'description' => 'High intensity ideological conflict.',
            'metadata' => ['devastation' => 'high']
        ]);
    }
}
