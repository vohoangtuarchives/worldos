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

// Autonomous Simulation Schedule (Character Layer)
Schedule::command('autonomous:tick')->everyMinute();

// Evolution Kernel Schedule (Physics Layer)
Schedule::command('world:tick')->everyMinute();

// Prune expired Sanctum tokens (validates expiration: DB cleanup)
Schedule::command('sanctum:prune-expired', ['--hours' => 24])->daily();
