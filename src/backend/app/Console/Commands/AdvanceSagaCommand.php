<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\WorldOS\Saga\Actions\AdvanceSagaAction;
use Illuminate\Console\Command;

/**
 * Artisan command to advance all Universes in a Saga.
 *
 * Usage: php artisan saga:advance {id} --ticks=5
 */
class AdvanceSagaCommand extends Command
{
    protected $signature = 'saga:advance {id : Saga UUID} {--ticks=1 : Number of ticks per Universe}';

    protected $description = 'Advance all Universes in a Saga by N ticks';

    public function handle(AdvanceSagaAction $action): int
    {
        $sagaId = $this->argument('id');
        $ticks = (int) $this->option('ticks');

        $this->info("Advancing Saga [{$sagaId}] by {$ticks} tick(s) per Universe...");

        try {
            $allResults = $action->handle($sagaId, $ticks);

            foreach ($allResults as $universeId => $results) {
                $this->line("  Universe [{$universeId}]: " . count($results) . ' tick(s)');

                $lastResult = end($results);
                if ($lastResult) {
                    $stability = round($lastResult->stability->value, 4);
                    $entropy = round($lastResult->newStateVector->entropy, 4);

                    $this->line("    Final: stability={$stability}, entropy={$entropy}");

                    if ($lastResult->collapseDetected) {
                        $this->error("    Collapsed: {$lastResult->collapseReason}");
                    }
                }
            }

            $totalTicks = array_sum(array_map('count', $allResults));
            $this->info("Done. Total ticks: {$totalTicks} across " . count($allResults) . ' universe(s)');

            return self::SUCCESS;
        } catch (\LogicException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
