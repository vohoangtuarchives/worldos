<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Domains\Narrative\Models\MaterialSeed;

class NarrativeMaterialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Power Systems
        $powerSystems = [
            [
                'type' => 'power_system',
                'name' => 'Qi Cultivation (Tu Tiên)',
                'description' => 'Absorb spiritual energy from the environment to ascend through realms.',
                'attributes' => ['complexity' => 'high', 'tone' => 'epic', 'scaling' => 'infinite'],
                'compatibility_tags' => ['ancient', 'Eastern fantasy', 'sects'],
            ],
            [
                'type' => 'power_system',
                'name' => 'The System (Hệ Thống)',
                'description' => 'A gamified interface that grants quests, skills, and stats.',
                'attributes' => ['complexity' => 'low', 'tone' => 'modern/meta', 'scaling' => 'linear'],
                'compatibility_tags' => ['modern', 'fantasy', 'isekai'],
            ],
            [
                'type' => 'power_system',
                'name' => 'Grimoire Magic',
                'description' => 'Magic spells recorded in personal books that grow with the user.',
                'attributes' => ['complexity' => 'medium', 'tone' => 'academic', 'scaling' => 'tiered'],
                'compatibility_tags' => ['Western fantasy', 'academy'],
            ],
        ];

        // 2. Social Structures
        $socialStructures = [
            [
                'type' => 'social_structure',
                'name' => 'Sect Hegemony (Tông Môn)',
                'description' => 'World ruled by powerful martial sects instead of governments.',
                'attributes' => ['conflict' => 'high', 'hierarchy' => 'rigid'],
                'compatibility_tags' => ['ancient', 'cultivation'],
            ],
            [
                'type' => 'social_structure',
                'name' => 'Megacorp Dystopia',
                'description' => 'Corporations own everything, including the air you breathe.',
                'attributes' => ['conflict' => 'medium', 'hierarchy' => 'wealth-based'],
                'compatibility_tags' => ['cyberpunk', 'modern'],
            ],
             [
                'type' => 'social_structure',
                'name' => 'Hunter Guilds',
                'description' => 'Freelance monster hunters organized into global guilds.',
                'attributes' => ['conflict' => 'variable', 'hierarchy' => 'merit-based'],
                'compatibility_tags' => ['modern', 'system'],
            ],
        ];

        // 3. Twists / Modifiers
        $twists = [
            [
                'type' => 'twist',
                'name' => 'Invincible Zone (Vô Địch Lĩnh Vực)',
                'description' => 'Protagonist is absolutely invincible within a small, fixed area (e.g., a shop).',
                'attributes' => ['tone' => 'comedic/power-fantasy', 'constraint' => 'spatial'],
                'compatibility_tags' => ['any'],
            ],
            [
                'type' => 'twist',
                'name' => 'Regression (Trọng Sinh)',
                'description' => 'Protagonist returns to the past with future knowledge.',
                'attributes' => ['tone' => 'revenge/strategic', 'constraint' => 'knowledge-based'],
                'compatibility_tags' => ['system', 'apocalypse'],
            ],
            [
                'type' => 'twist',
                'name' => 'Mana Scarcity (Mạt Pháp)',
                'description' => 'The world is running out of energy; using power shortens life.',
                'attributes' => ['tone' => 'desperate', 'constraint' => 'resource'],
                'compatibility_tags' => ['cultivation', 'magic'],
            ],
        ];

         // 4. Hidden Truths
        $hiddenTruths = [
            [
                'type' => 'hidden_truth',
                'name' => 'Livestream Universe',
                'description' => 'The entire world is a reality show for higher-dimensional beings.',
                'attributes' => ['tone' => 'meta/cosmic horror'],
                'compatibility_tags' => ['system', 'sci-fi'],
            ],
            [
                'type' => 'hidden_truth',
                'name' => 'Farm Planet',
                'description' => 'Humanity is being raised as cattle for an alien harvest.',
                'attributes' => ['tone' => 'dark/survival'],
                'compatibility_tags' => ['cultivation', 'sci-fi'],
            ],
        ];

        $allSeeds = array_merge($powerSystems, $socialStructures, $twists, $hiddenTruths);

        foreach ($allSeeds as $seed) {
            MaterialSeed::create(array_merge($seed, ['id' => Str::uuid()]));
        }
    }
}
