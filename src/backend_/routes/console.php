<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('world:crawl-materials {--world=} {--keywords=}', function () {
    $this->comment('Use the CrawlMaterialsCommand class instead.');
})->describe('Legacy stub. Actual logic in command class.');

// Autonomous Simulation Schedule (Character Layer - Legacy/Hybrid)
// DEPRECATED in V3: Micro-agents are temporarily disabled in favor of Macro-Civilization stats.
// Schedule::command('autonomous:tick')->everyMinute();

// Evolution Kernel Schedule (Physics Layer - V3 Engine)
// This is the SINGLE SOURCE OF TRUTH for WorldOS V3 Time.
Schedule::command('saga:advance-v3 --ticks=5')->everyMinute();

// Prune expired Sanctum tokens (validates expiration: DB cleanup)
Schedule::command('sanctum:prune-expired', ['--hours' => 24])->daily();
