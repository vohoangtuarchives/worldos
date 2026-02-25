<?php

namespace App\Console\Commands;

use App\Modules\Universe\Actions\SpawnUniverseAction;
use App\Modules\Universe\Dto\SpawnUniverseDTO;
use App\Modules\Narrative\Actions\CreateSagaAction;
use App\Modules\Narrative\Actions\AdvanceSagaAction;
use App\Modules\Narrative\Dto\CreateSagaDTO;
use App\Models\World;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestSagaQueueCommand extends Command
{
    protected $signature = 'saga:test-queue {ticks=3 : Number of ticks to advance}';
    protected $description = 'E2E test: Create World → Spawn Universe → Dispatch Async Saga Advance';

    public function handle(
        SpawnUniverseAction $spawnUniverse,
        CreateSagaAction $createSaga,
        AdvanceSagaAction $advanceSaga,
    ): int {
        $this->info('=== Phase 6: Async Saga Queue Test ===');

        // 1. Ensure a World exists (seeded once)
        $this->info('Step 1: Ensuring a World blueprint exists...');
        $worldModel = World::first();
        if (!$worldModel) {
            $worldModel = new World();
            $worldModel->name = 'Default Test World';
            $worldModel->preset_key = 'base';
            $worldModel->status = 'ACTIVE';
            $worldModel->law_vector = array_fill(0, 17, 0.1);
            $worldModel->config = [
                'a_matrix' => [0.0, 0.0, 0.0, 0.0],  // 2x2 zero matrix (stable)
                'l_matrix' => [1.0, -1.0, -1.0, 1.0], // 2x2 identity-like
                'alpha' => 0.5,
                'lambda' => 0.5,
                'eta' => 0.1,
                'beta' => 0.05,
            ];
            $worldModel->save();
            $this->info("  ✨ Created World: {$worldModel->id}");
        } else {
            $this->info("  ✅ Using existing World: {$worldModel->id}");
        }

        // 2. Spawn a Universe from that World
        $this->info('Step 2: Spawning Universe...');
        $dto = new SpawnUniverseDTO(worldId: $worldModel->id);
        $universe = $spawnUniverse->handle($dto);
        $universeId = $universe->getId()->value;
        $this->info("  ✅ Universe spawned: {$universeId}");

        // 3. Create a Saga linked to that Universe
        $this->info('Step 3: Creating Saga...');
        $sagaDto = new CreateSagaDTO(
            name: 'Async Test Saga',
            presetKey: null,
            universeIds: [$universeId],
        );
        $saga = $createSaga->handle($sagaDto);
        $sagaId = $saga->getId()->value;
        $this->info("  ✅ Saga created: {$sagaId}");

        // 4. Dispatch Async Advance (Non-blocking — returns 202 immediately)
        $ticks = (int) $this->argument('ticks');
        $this->info("Step 4: Dispatching AdvanceSagaJob for {$ticks} ticks (non-blocking)...");
        $result = $advanceSaga->handle(sagaId: $sagaId, ticks: $ticks);

        $this->info('  ✅ Dispatched! Response:');
        $this->line(json_encode($result, JSON_PRETTY_PRINT));

        $this->newLine();
        $this->comment('Now run a queue worker to process the queued tick jobs:');
        $this->line('  docker compose exec backend php artisan queue:work --once');
        $this->newLine();
        $this->comment('To stream Rust engine events in real-time:');
        $this->line('  docker compose exec backend php artisan simulation:listen');

        return Command::SUCCESS;
    }
}
