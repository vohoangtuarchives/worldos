<?php

namespace Database\Seeders;

use App\Models\World\WorldPreset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorldPresetSeeder extends Seeder
{
    public function run(): void
    {
        WorldPreset::create([
            'id' => Str::uuid()->toString(),
            'code' => 'sandbox_test',
            'name' => 'Sandbox Test World',
            'power_policy' => 'linear_power',
            'resource_policy' => 'infinite_resource',
            'conflict_policy' => 'no_conflict',
            'escalation_policy' => 'passive_escalation',
            'config' => ['debug' => true],
            'is_active' => true,
        ]);
    }
}
