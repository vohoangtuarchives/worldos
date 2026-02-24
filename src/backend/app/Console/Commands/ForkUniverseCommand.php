<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\WorldOS\Runtime\Actions\SpawnUniverseAction;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Dto\SpawnUniverseDTO;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\World\ValueObjects\WorldId;
use Illuminate\Console\Command;

/**
 * Artisan command: Fork a Universe from a specific tick.
 *
 * From docs §13.3: php artisan world:fork {world_id} {tick} "New Timeline"
 */
class ForkUniverseCommand extends Command
{
    protected $signature = 'world:fork
        {universe_id : UUID of the Universe to fork}
        {name? : Name for the forked timeline}';

    protected $description = 'Fork a Universe, creating a new timeline branch';

    public function handle(
        UniverseRepositoryInterface $universeRepository,
        SpawnUniverseAction $spawnAction,
    ): int {
        $sourceId = new UniverseId($this->argument('universe_id'));
        $name = $this->argument('name') ?? 'Fork_' . now()->format('Ymd_His');

        $this->info("🔀 Forking Universe: {$sourceId->value}");

        // Verify source exists
        $source = $universeRepository->findById($sourceId);
        if (!$source) {
            $this->error("Source Universe not found: {$sourceId->value}");

            return self::FAILURE;
        }

        $this->line("  Source Tick: {$source->getCurrentTick()}");
        $this->line("  Source Status: {$source->getStatus()->value}");

        // Spawn forked universe
        $dto = new SpawnUniverseDTO(
            worldId: $source->getWorldId(),
            name: $name,
            parentUniverseId: $sourceId->value,
        );

        $forked = $spawnAction->handle($dto);

        $this->newLine();
        $this->info('✅ Fork successful!');
        $this->line("  New Universe ID: {$forked->getId()->value}");
        $this->line("  Name: {$name}");
        $this->line("  Parent: {$sourceId->value}");

        return self::SUCCESS;
    }
}
