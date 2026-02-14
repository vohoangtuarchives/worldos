<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domains\Saga\DeepNarrativeAssembler;
use App\Domains\Saga\Author\AuthorRegistry;

$assembler = new DeepNarrativeAssembler();
$registry = new AuthorRegistry();

$events = [
    ['type' => 'famine_crisis', 'severity' => 9],
    ['type' => 'social_tension', 'severity' => 7],
];

echo "--- Style Comparison: WuxiaMaster vs DarkHistorian ---\n\n";

// 1. Wuxia Style
echo "--- PERSONA: WuxiaMaster ---\n";
$assembler->setPersona($registry->get('WuxiaMaster'));
echo $assembler->assemble($events, 2) . "\n\n";

// 2. Dark Style
echo "--- PERSONA: DarkHistorian ---\n";
$assembler->setPersona($registry->get('DarkHistorian'));
echo $assembler->assemble($events, 2) . "\n\n";
