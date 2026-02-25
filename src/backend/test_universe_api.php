<?php

require __DIR__ . '/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\World;
use Illuminate\Support\Str;
use App\Modules\Universe\Dto\SpawnUniverseDTO;
use App\Modules\Universe\Actions\SpawnUniverseAction;
use App\Modules\Universe\Dto\TickUniverseDTO;
use App\Modules\Universe\Actions\TickUniverseAction;

try {
    $world = World::create([
        'id' => Str::uuid()->toString(),
        'name' => 'API Action Test World',
        'preset_key' => 'base',
        'status' => 'ACTIVE',
        'law_vector' => [1,2,3],
        'config' => []
    ]);

    echo "Created World: " . $world->id . "\n";

    $spawnDto = new SpawnUniverseDTO(
        worldId: $world->id,
        name: 'Test Universe via Action',
        parameters: [
            'dimension' => 6,
            'aMatrix' => array_fill(0, 36, 0.0), // 6x6 Matrix
            'lMatrix' => array_fill(0, 36, 0.0), // 6x6 Matrix
            'alpha' => 0.1,
            'lambda' => 0.5,
            'eta' => 0.01,
            'beta' => 1.0,
            'deltaTarget' => 0.1,
            'gammaCap' => 2.0,
            'rMax' => 10.0,
            'energyRateLimit' => 1.5,
        ]
    );

    $spawnAction = $app->make(SpawnUniverseAction::class);
    $universe = $spawnAction->handle($spawnDto);

    echo "Spawned Universe: " . $universe->getId()->value . "\n";

    $tickDto = new TickUniverseDTO(
        universeId: $universe->getId()->value
    );

    $tickAction = $app->make(TickUniverseAction::class);
    $result = $tickAction->handle($tickDto);

    echo "Tick Result: \n";
    print_r($result);
    
    // Fetch from DB to verify cascade state changed
    $updatedUniverse = $app->make(\App\Modules\Universe\Contracts\UniverseRepositoryInterface::class)
        ->findById($universe->getId());
    
    echo "Updated Cascade State: \n";
    print_r($updatedUniverse->getCascadeState()->toArray());

    echo "\nSuccess!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
