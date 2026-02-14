<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\World\Actions\TickWorldAction;
use App\Domains\World\Aggregates\WorldAggregate;
use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use App\Domains\World\Repositories\WorldRepository;
use App\Domains\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class WorldTickCommand extends Command
{
    protected $signature = 'world:tick-autonomous {--world-id= : Specific world ID to tick} {--count=1 : Number of ticks to run} {--force : Force tick even if world is not autonomous} {--dry-run : Show what would happen without executing}';

    protected $description = 'Run autonomous world tick simulation';

    public function __construct(
        private readonly TickWorldAction $tickAction,
        private readonly WorldRepository $worldRepository,
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $worldId = $this->option('world-id');
        $count = (int) $this->option('count');
        $force = $this->option('force');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        try {
            if ($worldId) {
                $result = $this->tickSingleWorld($worldId, $count, $force, $isDryRun);
            } else {
                $result = $this->tickAllWorlds($count, $force, $isDryRun);
            }

            $this->displayResults($result);
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("World tick failed: {$e->getMessage()}");
            
            if (config('app.debug')) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function tickSingleWorld(string $worldId, int $count, bool $force, bool $isDryRun): TickSummary
    {
        $world = $this->worldRepository->findById($worldId);
        
        if (!$world) {
            throw new \InvalidArgumentException("World with ID {$worldId} not found");
        }

        if (!$force && !$world->isAutonomous()) {
            throw new \InvalidArgumentException("World {$worldId} is not in autonomous mode. Use --force to override.");
        }

        $characters = collect($this->characterRepository->findByWorldId($worldId));
        
        return $this->runTicks($world, $characters, $count, $isDryRun);
    }

    private function tickAllWorlds(int $count, bool $force, bool $isDryRun): TickSummary
    {
        $worlds = $force 
            ? $this->worldRepository->findAll()
            : $this->worldRepository->findAutonomous();

        $summary = new TickSummary();

        foreach ($worlds as $world) {
            $characters = collect($this->characterRepository->findByWorldId($world->id()));
            
            try {
                $worldSummary = $this->runTicks($world, $characters, $count, $isDryRun);
                $summary->merge($worldSummary);
            } catch (\Exception $e) {
                $this->error("Failed to tick world {$world->id()}: {$e->getMessage()}");
                $summary->addError($world->id(), $e->getMessage());
            }
        }

        return $summary;
    }

    private function runTicks(WorldAggregate $world, Collection $characters, int $count, bool $isDryRun): TickSummary
    {
        $summary = new TickSummary($world->id());

        for ($i = 1; $i <= $count; $i++) {
            $this->info("Processing tick {$i} for world {$world->id()}...");

            if ($isDryRun) {
                $this->line("  Would calculate entropy and generate shock events");
                $this->line("  Would check character survival for " . $characters->count() . " characters");
                $summary->addDryRun();
                continue;
            }

            $result = $this->tickAction->execute($world, $characters);
            
            $summary->addTick($result);

            // Display progress
            $this->line("  Tick {$result->tick}: Entropy {$result->entropy->value()}, " . 
                       count($result->shockEvents) . " shock events, " .
                       $result->getDeathCount() . " deaths");

            // Update world reference for next tick
            $world = $result->world;

            // Update characters who died
            $characters = $characters->map(function ($character) use ($result) {
                $deathResult = $result->survivalResults->first(
                    fn($r) => $r->character->characterId() === $character->characterId() && !$r->survived
                );
                
                return $deathResult ? $deathResult->character : $character;
            });
        }

        return $summary;
    }

    private function displayResults(TickSummary $summary): void
    {
        $this->newLine();
        $this->info('=== WORLD TICK SUMMARY ===');
        
        if ($summary->isDryRun()) {
            $this->info("Dry run completed - {$summary->getDryRunCount()} ticks would be processed");
            return;
        }

        $this->info("Worlds processed: {$summary->getWorldCount()}");
        $this->info("Total ticks: {$summary->getTotalTicks()}");
        $this->info("Total deaths: {$summary->getTotalDeaths()}");
        $this->info("Total shock events: {$summary->getTotalShockEvents()}");
        $this->info("Average execution time: " . number_format($summary->getAverageExecutionTime(), 3) . "s");

        if ($summary->hasErrors()) {
            $this->newLine();
            $this->error('ERRORS:');
            foreach ($summary->getErrors() as $worldId => $error) {
                $this->error("  World {$worldId}: {$error}");
            }
        }

        // Show world-specific details if only one world
        if ($summary->getWorldCount() === 1) {
            $this->newLine();
            $this->info('WORLD DETAILS:');
            $worldId = $summary->getFirstWorldId();
            $worldSummary = $summary->getWorldSummary($worldId);
            
            $this->line("  Final entropy: " . end($worldSummary['entropy']));
            $this->line("  Stability score: " . end($worldSummary['stability']));
            $this->line("  Survival rate: " . end($worldSummary['survival_rate']));
        }
    }
}

final class TickSummary
{
    private array $worldSummaries = [];
    private array $errors = [];
    private int $dryRunCount = 0;

    public function __construct(private readonly ?string $worldId = null) {}

    public function merge(TickSummary $other): void
    {
        $this->worldSummaries = array_merge($this->worldSummaries, $other->worldSummaries);
        $this->errors = array_merge($this->errors, $other->errors);
        $this->dryRunCount += $other->dryRunCount;
    }

    public function addTick($result): void
    {
        $worldId = $result->world->id();
        
        if (!isset($this->worldSummaries[$worldId])) {
            $this->worldSummaries[$worldId] = [
                'ticks' => 0,
                'deaths' => 0,
                'shock_events' => 0,
                'execution_time' => 0,
                'entropy' => [],
                'stability' => [],
                'survival_rate' => [],
            ];
        }

        $summary = &$this->worldSummaries[$worldId];
        $summary['ticks']++;
        $summary['deaths'] += $result->getDeathCount();
        $summary['shock_events'] += count($result->shockEvents);
        $summary['execution_time'] += $result->executionTime;
        $summary['entropy'][] = $result->entropy->value();
        $summary['stability'][] = $result->metrics->worldStability;
        $summary['survival_rate'][] = $result->metrics->survivalRate;
    }

    public function addDryRun(): void
    {
        $this->dryRunCount++;
    }

    public function addError(int|string $worldId, string $error): void
    {
        $this->errors[$worldId] = $error;
    }

    public function getWorldCount(): int
    {
        return count($this->worldSummaries);
    }

    public function getTotalTicks(): int
    {
        return array_sum(array_column($this->worldSummaries, 'ticks'));
    }

    public function getTotalDeaths(): int
    {
        return array_sum(array_column($this->worldSummaries, 'deaths'));
    }

    public function getTotalShockEvents(): int
    {
        return array_sum(array_column($this->worldSummaries, 'shock_events'));
    }

    public function getAverageExecutionTime(): float
    {
        $totalTime = array_sum(array_column($this->worldSummaries, 'execution_time'));
        $totalTicks = $this->getTotalTicks();
        
        return $totalTicks > 0 ? $totalTime / $totalTicks : 0;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isDryRun(): bool
    {
        return $this->dryRunCount > 0;
    }

    public function getDryRunCount(): int
    {
        return $this->dryRunCount;
    }

    public function getFirstWorldId(): int|string|null
    {
        return array_key_first($this->worldSummaries);
    }

    public function getWorldSummary(int|string $worldId): array
    {
        return $this->worldSummaries[$worldId] ?? [];
    }
}
