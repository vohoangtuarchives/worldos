<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\StoryEngine\Simulator;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use WorldOS\Blueprint\Domain\Legacy\Enums\PowerCeiling;
use WorldOS\Blueprint\Domain\Legacy\Enums\TechLevel;
use App\StoryEngine\Seed;

// 1. Create a Low Magic ("History") World Profile
echo "--- TEST 1: Low Magic World ---\n";
$lowMagicProfile = new WorldLawProfile(
    magicSystem: MagicSystemType::NONE,
    powerCeiling: PowerCeiling::HUMAN,
    cultivationAllowed: false,
    mythEmergenceEnabled: false,
    beliefToRealityRatio: 0.05,
    techLevel: TechLevel::MEDIEVAL,
    heavenlyWayStrength: 0.1 // Weak intervention
);

// Instantiate Simulator
$sim = new Simulator('test_law_low_magic');
// Inject Profile manually (hack for testing without DB)
$sim->world->lawProfile = $lowMagicProfile;

// Attempt to inject valid and invalid seeds manually into the loop?
// Better: We rely on the internal loop. But initial seed is POWER_GAP (valid).
// Let's force-inject a disallowed seed into $activeSeeds and see if RuleApplier or something filters it?
// Actually, new seeds are filtered in RuleApplier.
// Let's call RuleApplier manually to test Filter logic.

$validator = new \WorldOS\World\Application\Services\WorldLawValidator();

// Test A: Magic Seed in Low Magic World (Should be NULL)
$magicSeed = new Seed('MANA_STORM', 'world', 5);
$result = $validator->validateSeedApplication($lowMagicProfile, $magicSeed);
echo "Injecting MANA_STORM (Magic) -> Result: " . ($result ? "Allowed" : "REJECTED [Pass]") . "\n";

// Test B: Cultivation Seed in Low Magic World (Should be NULL)
$cultSeed = new Seed('TRIBULATION', 'personal', 9);
$result = $validator->validateSeedApplication($lowMagicProfile, $cultSeed);
echo "Injecting TRIBULATION (Cultivation) -> Result: " . ($result ? "Allowed" : "REJECTED [Pass]") . "\n";

// Test C: Power Seed > Ceiling (Should be CLAMPED)
$powerSeed = new Seed('WARLORD_RISE', 'faction', 9); // Severity 9
$result = $validator->validateSeedApplication($lowMagicProfile, $powerSeed);
if ($result) {
    echo "Injecting WARLORD_RISE (Severity 9) -> Result Severity: {$result->severity} (Expected <= 3) " . ($result->severity <= 3 ? "[Pass]" : "[FAIL]") . "\n";
} else {
    echo "Injecting WARLORD_RISE -> REJECTED (Unexpected)\n";
}

// 2. Create High Magic ("Xianxia") World
echo "\n--- TEST 2: High Magic World ---\n";
$highMagicProfile = WorldLawProfile::default(); // Xianxia
$sim2 = new Simulator('test_law_high_magic');
$sim2->world->lawProfile = $highMagicProfile;

// Test A: Magic Seed (Should be Allowed)
$result = $validator->validateSeedApplication($highMagicProfile, $magicSeed);
echo "Injecting MANA_STORM -> Result: " . ($result ? "Allowed [Pass]" : "REJECTED [Fail]") . "\n";

// --- TEST 3: Generator Integration (Claim Extractor Wiring) ---
echo "\n--- TEST 3: Generator Integration ---\n";

// Mock Dependencies
$mockLLM = new \App\StoryEngine\Services\FakeStoryLLMService();
// We need to bind things or just instantiate manually. Manual is faster for script.
$extractor = new \WorldOS\Legacy\Application\World\Services\RegexClaimExtractor();
$governance = new \App\Domains\WorldManagement\Services\AIGovernanceService();

$generator = new \App\StoryEngine\Services\StoryContentGenerator($mockLLM, $validator, $extractor, $governance);

if ($generator) {
    echo "StoryContentGenerator instantiated successfully with RegexClaimExtractor.\n";
    // We could run a generation, but FakeLLM might not return claim-laden text.
    // Just instantiation proves the type hint is compatible.
    echo "[PASS] Wiring checks out.\n";
} else {
    echo "[FAIL] Could not instantiate Generator.\n";
}

echo "\n--- VERIFICATION COMPLETE ---\n";
