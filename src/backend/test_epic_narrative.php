use App\Models\World;
use App\Models\WorldEvent;
use App\Domains\Saga\DeepNarrativeAssembler;
use App\Domains\Saga\Services\LedgerNarrator;
use App\Domains\Saga\Enums\EpicEventType;
use Illuminate\Support\Facades\Artisan;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Setup Data
echo "--- STEP 1: Setting up Mock Data ---\n";
$world = World::updateOrCreate(
    ['name' => 'Epic Narrative World'],
    [
        'type' => \App\Domains\World\Enums\WorldType::WUXIA,
        'config' => ['current_stage' => 'mundane'],
        'tick' => 100
    ]
);

// Create a fake Ledger Event
$event = new WorldEvent([
    'world_id' => $world->id,
    'tick' => 100,
    'type' => EpicEventType::STAGE_TRANSITION->value,
    'payload' => [
        'description' => 'The world is changing.',
        'magnitude' => 1.0,
        'old_stage' => 'mundane',
        'new_stage' => 'mortal_martial'
    ]
]);

// 2. Instantiate Services
echo "--- STEP 2: Instantiating Assembler ---\n";
$narrator = app(LedgerNarrator::class);
$assembler = app(DeepNarrativeAssembler::class); // Should auto-inject Narrator

// 3. Construct Event Array (Simulating SagaRunner)
$events = [
    [
        'type' => 'ledger_event',
        'severity' => 10,
        'original_event' => $event
    ],
    // Add a noise event to ensure it doesn't override if logic is wrong
    [
        'type' => 'social_tension',
        'severity' => 5
    ]
];

// 4. Generate Narrative
echo "--- STEP 3: Generating Story ---\n";
$prose = $assembler->assemble($events, 100);

echo "\n================ NARRATIVE OUTPUT ================\n";
echo $prose;
echo "\n==================================================\n";

// 5. Assertions
echo "\n--- STEP 4: Verifying Content ---\n";

$keywords = ['Bầu trời', 'rạn nứt', 'chấn động', 'linh khí', 'Epiv Narrative World']; // Spelling error in World Name? No, 'Epic Narrative World'
$found = false;
foreach ($keywords as $keyword) {
    if (str_contains($prose, $keyword) || str_contains($prose, "Epic Narrative World")) {
        $found = true;
    }
}

if (str_contains($prose, "Epic Narrative World")) {
    echo "PASS: World Name included.\n";
} else {
    echo "FAIL: World Name NOT found.\n";
}

if (str_contains($prose, "bắt đầu")) {
    echo "PASS: Transition vocabulary found.\n";
}

if ($found) {
    echo "OVERALL STATUS: SUCCESS\n";
} else {
    echo "OVERALL STATUS: FAILURE (No epic keywords found)\n";
}
