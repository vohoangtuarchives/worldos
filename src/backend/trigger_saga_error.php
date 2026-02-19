<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$saga = \App\Domains\Saga\Saga::latest()->first();
if (!$saga) {
    echo "No sagas found.\n";
    exit(1);
}
echo "Found saga: {$saga->id}\n";

$controller = app(\App\Http\Controllers\Api\Writer\WriterSagaController::class);
$request = \Illuminate\Http\Request::create("/api/writer/sagas/{$saga->id}/run", 'POST', [
    'tick_count' => 1,
    'evaluate_every' => 10
]);

try {
    $response = $controller->run($request, $saga->id);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
} catch (\Throwable $e) {
    echo "Caught in script: " . $e->getMessage() . "\n";
}
