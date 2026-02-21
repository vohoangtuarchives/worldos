<?php

namespace App\Console\Commands\Performance;

use Illuminate\Console\Command;
use Tuzy\Application\Material\State\CompressedSnapshotRepository;

/**
 * PruneSnapshotsCommand - Prune old snapshots to save storage
 */
class PruneSnapshotsCommand extends Command
{
    protected $signature = 'snapshots:prune
                            {world : World ID}
                            {--dry-run : Show what would be deleted}';

    protected $description = 'Prune old snapshots (keep every 10th, 100th, 1000th)';

    public function handle(): int
    {
        $worldId = (int) $this->argument('world');
        $dryRun = $this->option('dry-run');

        $repository = app(CompressedSnapshotRepository::class);

        if ($dryRun) {
            $this->info('DRY RUN - No snapshots will be deleted');
        }

        $this->info("Pruning snapshots for World {$worldId}...");

        $deleted = $repository->pruneSnapshots($worldId);

        if ($dryRun) {
            $this->info("Would delete {$deleted} snapshots");
        } else {
            $this->info("✓ Deleted {$deleted} snapshots");
        }

        // Show compression stats
        $stats = $repository->getCompressionStats($worldId);

        $this->newLine();
        $this->line('=== Compression Statistics ===');
        $this->line("Total Snapshots: {$stats['total_snapshots']}");
        $this->line("Original Size: " . $this->formatBytes($stats['original_size']));
        $this->line("Compressed Size: " . $this->formatBytes($stats['compressed_size']));
        $this->line("Compression Ratio: {$stats['compression_ratio']}%");
        $this->line("Space Saved: " . $this->formatBytes($stats['space_saved']));

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
