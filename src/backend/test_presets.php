<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = app(\App\Domains\Saga\Services\GenesisPresetService::class);
$c = $s->allByCategory();
fwrite(STDERR, 'OK: ' . count($c) . ' categories' . PHP_EOL);
