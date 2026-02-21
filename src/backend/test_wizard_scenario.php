<?php

use App\Models\World;
use App\Models\WorldEvent;
use App\Domains\Saga\Services\LedgerNarrator;
use Tuzy\Domain\Saga\Enums\EpicEventType;
use Tuzy\Domain\World\Enums\WorldType;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STEP 1: Setting up Wizarding World ---\n";
$narrator = app(LedgerNarrator::class);

// Mock World with MODERN_FANTASY type (matches GenesisPresetService update)
$world = new World();
$world->id = 999;
$world->name = "Anh Quoc Phap Thuat";
$world->type = WorldType::MODERN_FANTASY; 

echo "World Type: " . $world->type->value . "\n";

echo "\n--- STEP 2: Testing Myth Birth (Artifact Discovery) ---\n";
// Event: Myth Birth -> Artifact Discovery
$event1 = new WorldEvent([
    'world_id' => $world->id,
    'tick' => 100,
    'type' => EpicEventType::MYTH_BIRTH->value,
    'payload' => ['subtype' => 'artifact_discovery', 'magnitude' => 1.0, 'location' => 'Hẻm Xéo']
]);
$event1->setRelation('world', $world);

$text1 = $narrator->narrate($event1);
echo "Output: \"$text1\"\n";

if (str_contains($text1, 'đũa phép') || str_contains($text1, 'sách ma thuật') || str_contains($text1, 'Viên Đá Phù Thủy')) {
    echo "PASS: Wizarding keywords found.\n";
} else {
    echo "FAIL: Wizarding keywords missing.\n";
}

echo "\n--- STEP 3: Testing Great War (Clash of Empires) ---\n";
// Event: Great War -> Clash of Empires
$event2 = new WorldEvent([
    'world_id' => $world->id,
    'tick' => 200,
    'type' => EpicEventType::GREAT_WAR->value,
    'payload' => ['subtype' => 'clash_of_empires', 'magnitude' => 1.0, 'location' => 'Hogwarts']
]);
$event2->setRelation('world', $world);

$text2 = $narrator->narrate($event2);
echo "Output: \"$text2\"\n";

if (str_contains($text2, 'Thần Sáng') || str_contains($text2, 'Tử Thần Thực Tử') || str_contains($text2, 'Bộ Pháp Thuật')) {
    echo "PASS: Wizarding War keywords found.\n";
} else {
    echo "FAIL: Wizarding War keywords missing.\n";
}
