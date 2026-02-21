<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Tuzy\Domain\CognitiveKernel\KernelVersion;

class KernelVersionSeeder extends Seeder
{
    /**
     * Create initial kernel version snapshot (v1.0.0)
     */
    public function run(): void
    {
        KernelVersion::createSnapshot(
            version: '1.0.0',
            releaseNotes: 'Initial World OS kernel version with 28 core archetypes across 4 domains (perception, power, social, metaphysical)'
        );
    }
}
