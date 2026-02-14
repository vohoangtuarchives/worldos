<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Saga\Saga;

class TestSagaSeeder extends Seeder
{
    public function run(): void
    {
        Saga::create([
            'name' => 'Dynamic Time Test Saga',
            'status' => 'pending',
            'world_count' => 3
        ]);
    }
}
