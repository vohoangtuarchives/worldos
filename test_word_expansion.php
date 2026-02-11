<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domains\Saga\DeepNarrativeAssembler;

$assembler = new DeepNarrativeAssembler();

echo "--- Infinite Story Engine Verification ---\n";

$events = [
    ['type' => 'famine_crisis', 'severity' => 8],
    ['type' => 'social_tension', 'severity' => 6],
];

$output = $assembler->assemble($events, 1);

$wordCount = str_word_count(strip_tags($output));
$charCount = strlen($output);

echo "Sample Output Length: $charCount characters\n";
echo "Estimated Word Count: $wordCount words\n";
echo "\n--- Content Sample ---\n";
echo substr($output, 0, 1000) . "...\n";

if ($wordCount > 100) {
    echo "\n[PASS] Deep expansion is working (multiplier effect detected).\n";
} else {
    echo "\n[FAIL] Expansion factor too low.\n";
}
