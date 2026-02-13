<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attractors = [
            ['id' => '00000000-0000-0000-0000-000000000001', 'code' => 'EQUILIBRIUM', 'name' => 'Equilibrium'],
            ['id' => '00000000-0000-0000-0000-000000000002', 'code' => 'CHAOS', 'name' => 'Chaos'],
            ['id' => '00000000-0000-0000-0000-000000000003', 'code' => 'ORDER', 'name' => 'Order'],
            ['id' => '00000000-0000-0000-0000-000000000004', 'code' => 'TRANSCENDENCE', 'name' => 'Transcendence'],
            ['id' => '00000000-0000-0000-0000-000000000005', 'code' => 'STAGNATION', 'name' => 'Stagnation'],
        ];

        foreach ($attractors as $att) {
            DB::table('attractors')->updateOrInsert(
                ['id' => $att['id']],
                [
                    'code' => $att['code'],
                    'name' => $att['name'],
                    'lifecycle_state' => 'DOMINANT',
                    'historical_inertia' => json_encode([]),
                    'cumulative_rebirth_gain' => 0.0,
                    'identity_karma_index' => 0.0,
                    'phase_state' => 'STABLE',
                    'current_incarnation_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Seed initial incarnation for EQUILIBRIUM
        DB::table('attractor_incarnations')->updateOrInsert(
            ['id' => '10000000-0000-0000-0000-000000000001'],
            [
                'attractor_id' => '00000000-0000-0000-0000-000000000001',
                'parent_incarnation_id' => null,
                'start_tick' => 0,
                'end_tick' => null,
                'centroid_snapshot' => json_encode([
                    'entropy' => 0.3,
                    'energy' => 0.5,
                    'stability' => 0.7,
                    'strain' => 0.2,
                    'causality' => 0.6,
                ]),
                'semantic_snapshot' => json_encode([
                    'theme' => 'ORDER',
                    'archetype' => 'EQUILIBRIUM',
                    'mood' => 'CALM',
                ]),
                'basin_radius' => 0.15,
                'curvature_factor' => 1.5,
                'rebirth_gain_from_parent' => 0.0,
                'morph_intensity' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Update EQUILIBRIUM to reference its incarnation
        DB::table('attractors')
            ->where('id', '00000000-0000-0000-0000-000000000001')
            ->update(['current_incarnation_id' => '10000000-0000-0000-0000-000000000001']);
    }
}
