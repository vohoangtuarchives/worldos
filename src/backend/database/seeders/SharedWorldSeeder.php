<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SharedWorldSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Canon World
        $worldId = DB::table('worlds')->insertGetId([
            'name' => 'The Eternal Realm',
            'genre' => 'xianxia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Set Power Stage to Mortal Martial (Stage 1)
        DB::table('world_power_stages')->insert([
            'world_id' => $worldId,
            'current_stage' => 'mortal_martial',
            'accumulated_pressure' => 0.35,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Add History (Ledger)
        DB::table('world_event_ledger')->insert([
            ['world_id' => $worldId, 'event_type' => 'seal_vibration', 'magnitude' => 0.4, 'permanence' => 0.8, 'visibility' => 'public', 'epoch' => 50, 'created_at' => now()],
            ['world_id' => $worldId, 'event_type' => 'spirit_leak', 'magnitude' => 0.2, 'permanence' => 1.0, 'visibility' => 'public', 'epoch' => 75, 'created_at' => now()],
        ]);

        // 4. Create a "Main Story" (GLOBAL scope)
        DB::table('sagas')->insert([
            'id' => Str::uuid(),
            'world_id' => $worldId,
            'name' => 'Chronicles of the Breaking Seal',
            'power_scope' => 'global',
            'anchor_epoch' => 100,
            'anchor_stage' => 'mortal_martial',
            'created_at' => now(),
        ]);

        // 5. Create a "Side Story" (LOCAL scope)
        DB::table('sagas')->insert([
            'id' => Str::uuid(),
            'world_id' => $worldId,
            'name' => 'A Village in the Shadow of the Peak',
            'power_scope' => 'local',
            'anchor_epoch' => 102,
            'anchor_stage' => 'mundane',
            'created_at' => now(),
        ]);

        $this->command->info('✅ Shared World [The Eternal Realm] created with 2 stories and historical legends.');
    }
}
