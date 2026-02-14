<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\World\Repositories\WorldRepository;
use App\Domains\World\Services\WorldInitializer;
use App\Domains\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

final class WorldManageCommand extends Command
{
    protected $signature = 'world:manage 
                            {action : Action to perform (create|start|stop|status|reset|list)}
                            {--world-id= : World ID for specific actions}
                            {--name= : World name for creation}
                            {--preset=martial : World preset type}
                            {--autonomous : Enable autonomous mode}
                            {--force : Force action without confirmation}';

    protected $description = 'Manage WorldOS autonomous worlds';

    public function __construct(
        private readonly WorldRepository $worldRepository,
        private readonly WorldInitializer $worldInitializer,
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'create' => $this->createWorld(),
            'start' => $this->startWorld(),
            'stop' => $this->stopWorld(),
            'status' => $this->showStatus(),
            'reset' => $this->resetWorld(),
            'list' => $this->listWorlds(),
            default => $this->error("Unknown action: {$action}")
        };
    }

    private function createWorld(): int
    {
        $name = $this->option('name') ?: $this->ask('Enter world name:');
        $preset = $this->option('preset');
        $autonomous = $this->option('autonomous') ?: $this->confirm('Enable autonomous mode?');

        $this->info("Creating world '{$name}' with preset '{$preset}'...");

        try {
            $world = $this->worldInitializer->create([
                'name' => $name,
                'preset' => $preset,
                'autonomous' => $autonomous,
                'entropy' => 0.0,
                'tick' => 0,
            ]);

            $this->info("✅ World created with ID: {$world->id()}");
            
            if ($autonomous) {
                $this->info("🤖 Autonomous mode enabled");
                $this->info("💡 Run 'php artisan world:tick --world-id={$world->id()}' to start simulation");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to create world: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function startWorld(): int
    {
        $worldId = $this->option('world-id') ?: $this->ask('Enter world ID:');

        if (!$this->option('force') && !$this->confirm("Start world {$worldId}?")) {
            $this->info('Operation cancelled');
            return self::SUCCESS;
        }

        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                throw new \InvalidArgumentException("World {$worldId} not found");
            }

            $world = $world->enableAutonomous();
            $this->worldRepository->save($world);

            $this->info("✅ World {$worldId} started in autonomous mode");
            $this->info("💡 Run 'php artisan world:tick --world-id={$worldId}' to begin simulation");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to start world: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function stopWorld(): int
    {
        $worldId = $this->option('world-id') ?: $this->ask('Enter world ID:');

        if (!$this->option('force') && !$this->confirm("Stop world {$worldId}?")) {
            $this->info('Operation cancelled');
            return self::SUCCESS;
        }

        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                throw new \InvalidArgumentException("World {$worldId} not found");
            }

            $world = $world->disableAutonomous();
            $this->worldRepository->save($world);

            $this->info("✅ World {$worldId} stopped");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to stop world: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function showStatus(): int
    {
        $worldId = $this->option('world-id');

        if ($worldId) {
            return $this->showWorldStatus($worldId);
        } else {
            return $this->showAllWorldsStatus();
        }
    }

    private function showWorldStatus(string $worldId): int
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                throw new \InvalidArgumentException("World {$worldId} not found");
            }

            $characters = $this->characterRepository->findByWorldId($worldId);
            $aliveCount = count(array_filter($characters, fn($c) => $c->isAlive()));

            $this->info("🌍 WORLD STATUS");
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $world->id()],
                    ['Name', $world->name()],
                    ['Preset', $world->preset()],
                    ['Current Tick', $world->currentTick()],
                    ['Entropy Level', number_format($world->currentEntropy()->value(), 3)],
                    ['Autonomous', $world->isAutonomous() ? '✅ Yes' : '❌ No'],
                    ['Total Characters', count($characters)],
                    ['Alive Characters', $aliveCount],
                    ['Dead Characters', count($characters) - $aliveCount],
                    ['Last Tick', $world->lastTickAt()?->format('Y-m-d H:i:s') ?? 'Never'],
                    ['Created', $world->createdAt()->format('Y-m-d H:i:s')],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to get status: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function showAllWorldsStatus(): int
    {
        $worlds = $this->worldRepository->findAll();

        if ($worlds->isEmpty()) {
            $this->info('No worlds found');
            return self::SUCCESS;
        }

        $this->info("🌍 ALL WORLDS STATUS");
        
        $worldData = [];
        foreach ($worlds as $world) {
            $characters = $this->characterRepository->findByWorldId($world->id());
            $aliveCount = count(array_filter($characters, fn($c) => $c->isAlive()));

            $worldData[] = [
                $world->id(),
                $world->name(),
                $world->preset(),
                $world->currentTick(),
                number_format($world->currentEntropy()->value(), 3),
                $world->isAutonomous() ? '✅' : '❌',
                count($characters),
                $aliveCount,
            ];
        }

        $this->table(
            ['ID', 'Name', 'Preset', 'Tick', 'Entropy', 'Auto', 'Chars', 'Alive'],
            $worldData
        );

        return self::SUCCESS;
    }

    private function resetWorld(): int
    {
        $worldId = $this->option('world-id') ?: $this->ask('Enter world ID:');

        if (!$this->option('force') && !$this->confirm("⚠️  Reset world {$worldId}? This will clear all progress!")) {
            $this->info('Operation cancelled');
            return self::SUCCESS;
        }

        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                throw new \InvalidArgumentException("World {$worldId} not found");
            }

            // Reset world state
            $world = $world->reset();
            $this->worldRepository->save($world);

            // Reset characters
            $characters = $this->characterRepository->findByWorldId($worldId);
            foreach ($characters as $character) {
                $resetCharacter = $character->reset();
                $this->characterRepository->save($resetCharacter);
            }

            $this->info("✅ World {$worldId} has been reset");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to reset world: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function listWorlds(): int
    {
        $worlds = $this->worldRepository->findAll();

        if ($worlds->isEmpty()) {
            $this->info('No worlds found. Create one with: php artisan world:manage create');
            return self::SUCCESS;
        }

        $this->info("🌍 AVAILABLE WORLDS");
        
        foreach ($worlds as $world) {
            $status = $world->isAutonomous() ? '🤖 Autonomous' : '⏸️  Manual';
            $entropy = number_format($world->currentEntropy()->value(), 3);
            
            $this->line("  [{$world->id()}] {$world->name()} ({$world->preset()}) - {$status} - Entropy: {$entropy}");
        }

        $this->newLine();
        $this->info("Commands:");
        $this->info("  php artisan world:manage status --world-id=<id>");
        $this->info("  php artisan world:tick --world-id=<id>");
        $this->info("  php artisan world:manage start --world-id=<id>");

        return self::SUCCESS;
    }
}
