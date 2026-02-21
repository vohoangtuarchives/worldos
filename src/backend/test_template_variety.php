<?php

use App\Models\World;
use App\Models\WorldEvent;
use App\Domains\Saga\Services\NarrativeDictionary;
use App\Domains\Saga\Services\LedgerNarrator;
use Tuzy\Domain\Saga\Enums\EpicEventType;
use Tuzy\Domain\World\Enums\WorldType;
use Illuminate\Support\Str;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STEP 1: Verifying NarrativeDictionary Expansion ---\n";
$dict = new NarrativeDictionary();
$categories = ['discovery', 'betrayal', 'festival'];

foreach ($categories as $cat) {
    echo "Testing category: [$cat]\n";
    $templates = NarrativeDictionary::getTemplates()[$cat] ?? null;
    if ($templates) {
        $sample = $templates[2][0] ?? 'N/A';
        echo "  - Sample (Level 2): \"$sample\"\n";
        echo "  - PASS\n";
    } else {
        echo "  - FAIL: Category not found.\n";
    }
}

echo "\n--- STEP 2: Verifying LedgerNarrator Epic Flavors ---\n";
$narrator = app(LedgerNarrator::class);

// Mock Worlds
$wuxiaWorld = new World();
$wuxiaWorld->id = 1;
$wuxiaWorld->name = "Dai Viet Tien Gioi";
$wuxiaWorld->type = WorldType::WUXIA;

$scifiWorld = new World();
$scifiWorld->id = 2;
$scifiWorld->name = "Neo-Saigon 2077";
$scifiWorld->type = WorldType::SCIFI;

// Mock Event: Myth Birth / Artifact Discovery
$payload = [
    'subtype' => 'artifact_discovery',
    'magnitude' => 1.0,
    'description' => 'Something found'
];

$wuxiaEvent = new WorldEvent([
    'world_id' => $wuxiaWorld->id,
    'tick' => 100,
    'type' => EpicEventType::MYTH_BIRTH->value,
    'payload' => $payload
]);
$wuxiaEvent->setRelation('world', $wuxiaWorld);

$scifiEvent = new WorldEvent([
    'world_id' => $scifiWorld->id,
    'tick' => 100,
    'type' => EpicEventType::MYTH_BIRTH->value,
    'payload' => $payload
]);
$scifiEvent->setRelation('world', $scifiWorld);

// Test Wuxia
$wuxiaText = $narrator->narrate($wuxiaEvent);
echo "WUXIA Output: \"$wuxiaText\"\n";

if (str_contains($wuxiaText, 'Thánh Vật') || str_contains($wuxiaText, 'cổ vật') || str_contains($wuxiaText, 'thần binh')) {
    echo "  - PASS: Wuxia keywords found.\n";
} else {
    echo "  - FAIL: Wuxia keywords missing.\n";
}

// Test Sci-Fi
$scifiText = $narrator->narrate($scifiEvent);
echo "SCIFI Output: \"$scifiText\"\n";

if (str_contains($scifiText, 'cảm biến') || str_contains($scifiText, 'phi thuyền') || str_contains($scifiText, 'tín hiệu')) {
    echo "  - PASS: Sci-Fi keywords found.\n";
} else {
    echo "  - FAIL: Sci-Fi keywords missing.\n";
}

echo "\n--- STEP 3: Verifying New Epic Event Types (Cataclysm) ---\n";
$cataclysmEvent = new WorldEvent([
    'world_id' => $wuxiaWorld->id,
    'tick' => 100,
    'type' => EpicEventType::CATACLYSM->value,
    'payload' => ['subtype' => 'natural_disaster', 'magnitude' => 1.0]
]);
$cataclysmEvent->setRelation('world', $wuxiaWorld);

$cataclysmText = $narrator->narrate($cataclysmEvent);
echo "Cataclysm Output: \"$cataclysmText\"\n";

if (str_contains($cataclysmText, 'nứt toác') || str_contains($cataclysmText, 'hồng thủy') || str_contains($cataclysmText, 'Núi lửa')) {
    echo "  - PASS: Cataclysm keywords found.\n";
} else {
    echo "  - FAIL: Cataclysm keywords missing.\n";
}
