<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\WorldOS\Runtime\Actions\AdvanceUniverseAction;
use Illuminate\Console\Command;

/**
 * Artisan command to advance a Universe by N ticks.
 *
 * Usage: php artisan universe:tick {id} --ticks=5
 */
class TickUniverseCommand extends Command
{
    protected $signature = 'universe:tick {id : Universe UUID} {--ticks=1 : Number of ticks to advance}';

    protected $description = 'Advance a Universe simulation by N ticks';

    public function handle(AdvanceUniverseAction $action): int
    {
        $universeId = $this->argument('id');
        $ticks = (int) $this->option('ticks');

        $this->info("Advancing Universe [{$universeId}] by {$ticks} tick(s)...");

        try {
            $results = $action->handle($universeId, $ticks);

            foreach ($results as $i => $result) {
                $tick = $i + 1;
                $stability = round($result->stability->value, 4);
                $entropy = round($result->newStateVector->entropy, 4);
                $collapsed = $result->collapseDetected ? ' [COLLAPSED]' : '';
                $transitions = count($result->phaseTransitions);

                $this->line(
                    "  Tick {$tick}: stability={$stability}, entropy={$entropy}, transitions={$transitions}{$collapsed}"
                );

                if ($result->collapseDetected) {
                    $this->error("  Collapse detected: {$result->collapseReason}");
                    break;
                }
            }

            $this->info('Done. Total ticks executed: ' . count($results));

            return self::SUCCESS;
        } catch (\LogicException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
