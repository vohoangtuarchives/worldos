<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Vietnamese\Models\ScoringVersion;
use App\Domains\Vietnamese\Config\EventDimensionMapping;

class ScoringVersionSeeder extends Seeder
{
    public function run(): void
    {
        ScoringVersion::create([
            'version' => 'v1.0',
            'dimension_weights' => [
                'military' => 1.2,
                'governance' => 1.1,
                'territory' => 1.1,
                'philosophy' => 1.0,
                'education' => 0.9,
                'culture' => 1.0,
                'spirituality' => 0.8,
                'rebellion' => 1.0,
                'reform' => 1.0,
                'diplomacy' => 0.8,
                'economic' => 0.8,
                'mythic' => 0.7,
            ],
            'event_dimension_map' => EventDimensionMapping::MAP,
            'config' => [
                'normalization_constant' => 5.0,
                'time_decay_lambda' => 0.0003,
            ],
            'is_active' => true,
            'changelog' => 'Initial version with balanced weights favoring military/governance slightly',
        ]);
    }
}
