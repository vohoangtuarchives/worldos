<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use WorldOS\Domains\Evolution\Services\WorldEvolutionPipeline;
use WorldOS\Domains\Evolution\ValueObjects\WorldSnapshot;

try {
    // Mock Bindings
    app()->instance(\WorldOS\Domains\Evolution\Contracts\AttractorRepositoryInterface::class, new \WorldOS\Infrastructure\Persistence\Evolution\InMemoryAttractorRepository());
    app()->instance(\WorldOS\Domains\Evolution\Contracts\EntropyLedgerInterface::class, new \WorldOS\Infrastructure\Persistence\Evolution\InMemoryEntropyLedger());
    
    $pipeline = app(WorldEvolutionPipeline::class);
    echo "Pipeline resolved successfully.\n";
    
    // Try a dummy step if possible, but just resolving is often where it fails if DI is broken
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
