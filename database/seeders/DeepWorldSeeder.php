<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\World\Myth;
use App\Domains\World\Scar;

class DeepWorldSeeder extends Seeder
{
    public function run()
    {
        $worldId = DB::table('worlds')->first()->id ?? 1;

        // 1. Seed Myths (Immutable Truths)
        Myth::create([
            'world_id' => $worldId,
            'truth_statement' => 'The Veil of Ignorance is absolute. No mortal may perceive the true form of a God.',
            'rigidity' => 1.0,
            'origin_event_id' => 'genesis_01'
        ]);

        Myth::create([
            'world_id' => $worldId,
            'truth_statement' => 'Technology halts at the Quantum Barrier. Artificial General Intelligence is impossible.',
            'rigidity' => 0.9,
            'origin_event_id' => 'butlerian_jihad_echo'
        ]);

        // 2. Seed Scars (Permanent Damage/Constraints)
        Scar::create([
            'world_id' => $worldId,
            'location_scope' => 'Global',
            'constraint_rule' => 'Vampires cannot cross running water.',
            'severity' => 0.6,
            'origin_event_id' => 'curse_of_blood_era'
        ]);

        Scar::create([
            'world_id' => $worldId,
            'location_scope' => 'Sector 7',
            'constraint_rule' => 'Reality is thin here. Spirits manifest easily.',
            'severity' => 0.4,
            'origin_event_id' => 'rift_accident_2024'
        ]);
        
        // 3. Seed some initial Contradiction Memory?
        // Let's leave it empty so AI learns from scratch.
    }
}
