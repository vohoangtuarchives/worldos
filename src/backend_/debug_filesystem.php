<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Storage::disk('local')->put('test.txt', 'hello');
    echo "Disk 'local' OK\n";
    echo "Path: " . \Illuminate\Support\Facades\Storage::disk('local')->path('test.txt') . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
