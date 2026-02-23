<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\World;
use App\Models\Story;
use App\StoryEngine\Seed;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use App\StoryEngine\Services\StoryContentGenerator;
use Illuminate\Support\Facades\DB;

// 1. Setup World & Story
echo "--- TEST: AI Governance (ADR-0007) ---\n";
$world = World::create([
    'name' => 'Governance Test World',
    'law_profile' => WorldLawProfile::default()
]);

$story = new Story();
$story->title = "Test Story";
$story->world_id = $world->id;
$story->world_state = []; // Dummy
$story->character_state = ['powerTier' => 1]; // Dummy

// 2. Mock LLM Provider
// We need a mock that returns content with Claims.
$mockLLM = new class implements \App\Domains\Narrative\LLM\Contracts\LLMProvider {
    public function generate(string $systemPrompt, string $userPrompt): array {
        return [
            'title' => 'The Forbidden Spell',
            'content' => "The protagonist Cast a Spell of fire. Then he Broke through to the Nascent Soul realm."
        ];
    }
};

// 3. Instantiate Generator with Real Services
$validator = new \WorldOS\World\Application\Services\WorldLawValidator();
$extractor = new \App\Domains\World\Services\RegexClaimExtractor();
$governance = new \App\Domains\WorldManagement\Services\AIGovernanceService();

$generator = new StoryContentGenerator($mockLLM, $validator, $extractor, $governance);

// 4. Run Generation
echo "Generating Content...\n";
$seed = new Seed('POWER_GAP', 'personal', 5);
$result = $generator->generate($story, $seed);

echo "Result Title: " . $result['title'] . "\n";

// 5. Verify Database Logs
$genLog = DB::table('ai_generations')->where('world_id', $world->id)->first();
if ($genLog) {
    echo "[PASS] AI Generation Logged. ID: {$genLog->id}, Status: {$genLog->status}\n";
    echo "Prompt Hash: {$genLog->prompt_hash}\n";
    
    $claims = DB::table('ai_extracted_claims')->where('generation_id', $genLog->id)->get();
    echo "[PASS] Generated " . count($claims) . " Claims.\n";
    foreach ($claims as $c) {
        echo "- Claim: {$c->claim_type} (Valid: {$c->is_valid})\n";
    }
} else {
    echo "[FAIL] No AI Generation Logged.\n";
}

// Cleanup
// DB::table('ai_extracted_claims')->truncate();
// DB::table('ai_generations')->truncate();
// $world->delete();
