<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\World;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use App\Domains\World\Services\WorldForkService;
use WorldOS\World\Application\Services\WorldLawValidator;
use App\StoryEngine\Seed;

// 1. Setup Parent World (Low Magic)
echo "--- TEST: Law Forking (ADR-0006) ---\n";
$parentProfile = WorldLawProfile::default();
$parentProfile->magicSystem = MagicSystemType::NONE; // No Magic
$parentProfile->cultivationAllowed = false;

// Mock Parent World
$parentWorld = new World(['name' => 'Parent Timeline (Low Magic)']);
$parentWorld->law_profile = $parentProfile;
$parentWorld->id = 999; // Fake ID
// We can't actually save without DB, so we'll mock the repo usage or just test the logic objects?
// WorldForkService relies on DB transactions and queries. 
// We cannot run WorldForkService without a real DB.
// Current environment seems to support DB (SQLite or similar). 
// Let's try to actually create them if Migrations ran.

try {
    $parentWorld->save();
    echo "Parent World Created: {$parentWorld->name} (ID: {$parentWorld->id})\n";
    echo "Parent Magic System: {$parentWorld->law_profile->magicSystem->value}\n";

    // 2. Prepare Fork Profile (High Magic)
    $childProfile = WorldLawProfile::default();
    $childProfile->magicSystem = MagicSystemType::SPIRITUAL_QI; // Magic Allowed!
    $childProfile->cultivationAllowed = true;

    // 3. Execute Fork
    echo "Forking to 'Child Timeline (High Magic)'...\n";
    $forkService = new WorldForkService();
    $childWorld = $forkService->fork($parentWorld, 0, 'Child Timeline (High Magic)', $childProfile);

    echo "Child World Created: {$childWorld->name} (ID: {$childWorld->id})\n";
    echo "Child Magic System: {$childWorld->law_profile->magicSystem->value}\n";

    // 4. Verify Behavior Difference
    $validator = new WorldLawValidator();
    $magicSeed = new Seed('MANA_STORM', 'world', 5);

    $parentCheck = $validator->validateSeedApplication($parentWorld->law_profile, $magicSeed);
    echo "Injecting Magic into Parent: " . ($parentCheck ? "Allowed [FAIL]" : "REJECTED [PASS]") . "\n";

    $childCheck = $validator->validateSeedApplication($childWorld->law_profile, $magicSeed);
    echo "Injecting Magic into Child: " . ($childCheck ? "Allowed [PASS]" : "REJECTED [FAIL]") . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
